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

class ReservationController extends AbstractController
{
    // =====================================================================
    // 1. NOUVELLE RÉSERVATION (Achat du parcours complet avec Stripe)
    // =====================================================================
    #[Route('/reserver/{id}', name: 'app_reservation_new')]
    #[IsGranted('ROLE_USER')]
    public function reserver(Prestation $prestation, Request $request): Response
    {
        // Initialisation factice pour le formulaire
        $premiereSeance = new Seance();
        $premiereSeance->setPrestation($prestation);

        $form = $this->createForm(ReservationType::class, $premiereSeance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // 1. On stocke les informations vitales dans la session de l'utilisateur
            $session = $request->getSession();
            $session->set('reservation_en_cours', [
                'prestation_id' => $prestation->getId(),
                'date_rendez_vous' => $premiereSeance->getDateRendezVous()->format('Y-m-d H:i:s') 
            ]);

            // 2. On redirige vers notre contrôleur de paiement Stripe
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
        // SÉCURITÉ : On vérifie que la séance appartient bien à l'utilisateur connecté
        if ($seance->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à planifier cette séance.');
        }

        $form = $this->createForm(ReservationType::class, $seance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $seance->setStatut('En attente de validation');
            $entityManager->flush();

            // Envoi du mail de notification à l'administrateur
            $email = (new TemplatedEmail())
                ->from('noreply@metamorphysis.com')
                ->to('admin@metamorphysis.com')
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
            'seance' => $seance, // Permet au JavaScript de cibler l'ID de la séance
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

    /**
     * Route conservée temporairement pour la rétrocompatibilité si un élément pointe encore sur l'ancienne URL API
     */
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