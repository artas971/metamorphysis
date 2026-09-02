<?php

namespace App\Controller;

use App\Entity\Prestation;
use App\Entity\Seance;
use App\Entity\User;
use App\Form\ReservationType;
use App\Service\PlanningService;
use App\Service\DailyCoService;
use App\Repository\SeanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ReservationController extends AbstractController
{


    #[Route('/reserver/{slug}', name: 'app_reservation_new')]
    #[IsGranted('ROLE_USER')]
    public function reserver(string $slug, \App\Repository\PrestationRepository $prestationRepository, Request $request, CacheInterface $cache): Response
    {
        $prestation = is_numeric($slug) 
            ? $prestationRepository->find((int) $slug) 
            : $prestationRepository->findOneBy(['slug' => $slug]);

        if (!$prestation) {
            throw $this->createNotFoundException('Prestation introuvable.');
        }

        // Si l'accès s'est fait par ID numérique et qu'un slug existe, redirection canonique 301
        if (is_numeric($slug) && $prestation->getSlug()) {
            return $this->redirectToRoute('app_reservation_new', [
                'slug' => $prestation->getSlug(),
                'personnes' => $request->query->get('personnes')
            ], Response::HTTP_MOVED_PERMANENTLY);
        }

        /** @var User|null $user */
        $user = $this->getUser();

        // 1. Prérequis strict : Consultation Initiale préalable obligatoire
        if ($user && !$user->canBookPrestation($prestation)) {
            $this->addFlash('warning', 'Afin d\'adapter au mieux votre accompagnement, vous devez préalablement réaliser une Consultation Initiale avant de pouvoir réserver d\'autres types de soins.');
            return $this->redirectToRoute('app_prestation_show', ['slug' => 'consultation-initiale']);
        }

        // 2. Accompagnement déjà en cours
        if ($user && $user->hasActivePrestation($prestation)) {
            $this->addFlash('warning', 'Vous suivez déjà un accompagnement en cours pour la prestation "' . $prestation->getNom() . '". Vous devez terminer vos séances actuelles avant de pouvoir reprendre ce même type de prestation.');
            return $this->redirectToRoute('app_account');
        }

        $nombrePersonnes = (int) ($request->query->get('personnes') ?? $request->request->get('personnes', 1));
        if ($nombrePersonnes < 1) {
            $nombrePersonnes = 1;
        }

        $montant = $prestation->calculerPrixTotal($nombrePersonnes);

        $premiereSeance = new Seance();
        $premiereSeance->setPrestation($prestation);
        $premiereSeance->setDuree($prestation->getDuree() ?? 60); // Sécurité durée
        $premiereSeance->setNombrePersonnes($nombrePersonnes);
        $premiereSeance->setMontantPaye($montant);

        $form = $this->createForm(ReservationType::class, $premiereSeance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateRendezVous = $premiereSeance->getDateRendezVous();
            
            if ($dateRendezVous) {
                $delaiMin6h = (new \DateTime())->modify('+6 hours');
                if ($dateRendezVous < $delaiMin6h) {
                    $this->addFlash('danger', 'Les rendez-vous doivent être réservés au moins 6 heures à l\'avance.');
                    return $this->redirectToRoute('app_reservation_new', ['slug' => $prestation->getSlug() ?? $prestation->getId(), 'personnes' => $nombrePersonnes]);
                }
            }

            $lockKey = 'lock_' . $dateRendezVous->format('Y-m-d_H-i');
            $cache->get($lockKey, function (ItemInterface $item) {
                $item->expiresAfter(180);
                return true; 
            });

            $session = $request->getSession();
            $session->set('reservation_en_cours', [
                'prestation_id' => $prestation->getId(),
                'date_rendez_vous' => $dateRendezVous->format('Y-m-d H:i:s'),
                'nombre_personnes' => $nombrePersonnes,
                'montant' => $montant,
            ]);

            return $this->redirectToRoute('app_stripe_checkout'); 
        }

        return $this->render('reservation/index.html.twig', [
            'form' => $form->createView(),
            'prestation' => $prestation,
            'seance' => clone $premiereSeance,
            'nombrePersonnes' => $nombrePersonnes,
            'montant' => $montant,
        ]);
    }

    #[Route('/planifier-ma-seance/{id}', name: 'app_seance_planifier')]
    #[IsGranted('ROLE_USER')]
    public function planifier(
        Seance $seance, 
        Request $request, 
        EntityManagerInterface $entityManager, 
        \App\Service\BookingMailerService $bookingMailer,
        DailyCoService $dailyCoService 
    ): Response {
        if ($seance->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à planifier cette séance.');
        }

        $form = $this->createForm(ReservationType::class, $seance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nouvelleDate = $seance->getDateRendezVous();
            
            if ($nouvelleDate) {
                $delaiMin6h = (new \DateTime())->modify('+6 hours');
                if ($nouvelleDate < $delaiMin6h) {
                    $this->addFlash('danger', 'Les rendez-vous doivent être programmés au moins 6 heures à l\'avance.');
                    return $this->redirectToRoute('app_seance_planifier', ['id' => $seance->getId()]);
                }

                $debutJournee = (clone $nouvelleDate)->setTime(0, 0, 0);
                $finJournee = (clone $nouvelleDate)->setTime(23, 59, 59);

                $seanceExistante = $entityManager->getRepository(Seance::class)->createQueryBuilder('s')
                    ->where('s.user = :user')
                    ->andWhere('s.id != :id') 
                    ->andWhere('s.dateRendezVous >= :debut AND s.dateRendezVous <= :fin')
                    ->andWhere('s.statut != :statutNonPlanifiee')
                    ->setParameter('user', $this->getUser())
                    ->setParameter('id', $seance->getId())
                    ->setParameter('debut', $debutJournee)
                    ->setParameter('fin', $finJournee)
                    ->setParameter('statutNonPlanifiee', 'Non planifiée')
                    ->getQuery()
                    ->getResult();

                if (count($seanceExistante) > 0) {
                    $this->addFlash('warning', 'Vous avez déjà une séance de prévue à cette date. Veuillez choisir un autre jour.');
                    return $this->redirectToRoute('app_seance_planifier', ['id' => $seance->getId()]);
                }
            }

            $seance->setStatut('En attente de validation');

            // Sécurité durée
            if (!$seance->getDuree() && $seance->getPrestation()) {
                $seance->setDuree($seance->getPrestation()->getDuree() ?? 60);
            }

            $lienVisio = $dailyCoService->createRoom($seance->getDateRendezVous());
            if ($lienVisio) {
                $seance->setLienVisio($lienVisio);
            }

            $entityManager->flush();

            // 1. Email Client (Demande de RDV bien enregistrée, en attente de confirmation par Louisa)
            $bookingMailer->sendPendingBookingToClient($seance);

            // 2. Email Admin (Notification pour Louisa qu'une séance est demandée)
            $bookingMailer->sendNewBookingPaidToAdmin($seance);

            $this->addFlash('success', 'Votre séance n°' . $seance->getNumero() . ' a été enregistrée avec succès. Vous recevrez un e-mail de confirmation dès sa validation.');
            return $this->redirectToRoute('app_account');
        }

        return $this->render('reservation/index.html.twig', [
            'form' => $form->createView(),
            'prestation' => $seance->getPrestation(),
            'seance' => $seance,
        ]);
    }

    #[Route('/seance/annuler/{id}', name: 'app_seance_annuler', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function annuler(
        Seance $seance, 
        Request $request, 
        EntityManagerInterface $entityManager, 
        \App\Service\BookingMailerService $bookingMailer
    ): Response
    {
        if ($seance->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas annuler cette séance.');
        }

        if ($seance->getDateRendezVous()) {
            $maintenant = new \DateTime();
            $limiteAnnulation = (clone $seance->getDateRendezVous())->modify('-48 hours');

            if ($maintenant > $limiteAnnulation) {
                $this->addFlash('danger', 'Vous ne pouvez pas annuler ou reporter une séance à moins de 48 heures de celle-ci.');
                return $this->redirectToRoute('app_account');
            }
        }

        // Mémorisation de la date avant de l'effacer pour pouvoir l'afficher dans l'e-mail
        $ancienneDate = $seance->getDateRendezVous();

        $seance->setDateRendezVous(null);
        $seance->setLienVisio(null);
        $seance->setStatut('Non planifiée');

        $entityManager->flush();

        // Envoi des e-mails d'annulation
        $bookingMailer->sendCancellationToClient($seance, $ancienneDate);
        $bookingMailer->sendCancellationToAdmin($seance, $ancienneDate);

        $this->addFlash('success', 'Votre séance a été annulée avec succès. Vous pouvez la replanifier dès maintenant dans votre espace.');
        return $this->redirectToRoute('app_account');
    }

    #[Route('/reservation/{id}', name: 'app_reservation_show')]
    #[IsGranted('ROLE_USER')]
    public function show(Seance $seance): Response
    {
        if ($seance->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir ce rendez-vous.');
        }

        return $this->render('reservation/show.html.twig', [
            'reservation' => $seance, 
        ]);
    }

    #[Route('/api/disponibilites/seance/{id}/{date}', name: 'api_disponibilites_seance', methods: ['GET'])]
    public function getDisponibilitesSeance(Seance $seance, string $date, PlanningService $planningService): JsonResponse
    {
        try {
            $dateRecherchee = new \DateTime($date);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Format de date invalide'], 400);
        }

        $dureeReelle = $seance->getDuree() ?? 60; 
        $creneaux = $planningService->getCreneauxDisponibles($dateRecherchee, $dureeReelle);

        return new JsonResponse($creneaux);
    }

    #[Route('/api/disponibilites/{id}/{date}', name: 'api_disponibilites', methods: ['GET'])]
    public function getDisponibilites(Prestation $prestation, string $date, PlanningService $planningService): JsonResponse
    {
        try {
            $dateRecherchee = new \DateTime($date);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Format de date invalide'], 400);
        }

        $dureeReelle = $prestation->getDuree() ?? 60; 
        $creneaux = $planningService->getCreneauxDisponibles($dateRecherchee, $dureeReelle);

        return new JsonResponse($creneaux);
    }
}