<?php

namespace App\Controller;

use App\Entity\Prestation;
use App\Entity\Seance;
use App\Form\ReservationType;
use App\Service\PlanningService;
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
    // =====================================================================
    // 1. NOUVELLE RÉSERVATION (Achat du parcours complet avec Stripe)
    // =====================================================================
    #[Route('/reserver/{id}', name: 'app_reservation_new')]
    #[IsGranted('ROLE_USER')]
    public function reserver(Prestation $prestation, Request $request, CacheInterface $cache): Response
    {
        $premiereSeance = new Seance();
        $premiereSeance->setPrestation($prestation);

        $form = $this->createForm(ReservationType::class, $premiereSeance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $dateRendezVous = $premiereSeance->getDateRendezVous();
            
            // --- SYSTÈME ANTI-DOUBLE RÉSERVATION (VERROU DE 3 MINUTES) ---
            // On crée une clé unique de cache basée sur la date et l'heure (ex: lock_2026-07-22_10-00)
            $lockKey = 'lock_' . $dateRendezVous->format('Y-m-d_H-i');
            
            // On verrouille ce créneau pour 3 minutes (180 secondes)
            $cache->get($lockKey, function (ItemInterface $item) {
                $item->expiresAfter(180);
                return true; 
            });
            // -------------------------------------------------------------

            $session = $request->getSession();
            $session->set('reservation_en_cours', [
                'prestation_id' => $prestation->getId(),
                'date_rendez_vous' => $dateRendezVous->format('Y-m-d H:i:s') 
            ]);

            return $this->redirectToRoute('app_stripe_checkout'); 
        }

        return $this->render('reservation/index.html.twig', [
            'form' => $form->createView(),
            'prestation' => $prestation,
            'seance' => clone $premiereSeance,
        ]);
    }

    // =====================================================================
    // 2. PLANIFICATION (Placer une date sur une séance déjà achetée)
    // =====================================================================
    #[Route('/planifier-ma-seance/{id}', name: 'app_seance_planifier')]
    #[IsGranted('ROLE_USER')]
    public function planifier(Seance $seance, Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if ($seance->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à planifier cette séance.');
        }

        $form = $this->createForm(ReservationType::class, $seance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $seance->setStatut('En attente de validation');
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from('noreply@metamorphysis.com')
                ->to('Metamorphysisconsulting@gmail.com')
                ->subject('Nouvelle planification : Séance ' . $seance->getNumero() . ' - ' . $seance->getPrestation()->getNom())
                ->htmlTemplate('emails/nouvelle_reservation.html.twig')
                ->context([
                    'seance' => $seance,
                    'client' => $this->getUser(),
                    'prestation' => $seance->getPrestation()
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Votre séance n°' . $seance->getNumero() . ' a été planifiée avec succès.');
            return $this->redirectToRoute('app_account');
        }

        return $this->render('reservation/index.html.twig', [
            'form' => $form->createView(),
            'prestation' => $seance->getPrestation(),
            'seance' => $seance,
        ]);
    }

    // =====================================================================
    // 3. AFFICHAGE DES DÉTAILS D'UNE SÉANCE
    // =====================================================================
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

    // =====================================================================
    // 4. API : GESTION DES DISPONIBILITÉS DU CALENDRIER
    // =====================================================================
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