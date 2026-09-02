<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Repository\PrestationRepository;
use App\Repository\SeanceRepository;
use App\Repository\UserRepository;
use App\Service\DailyCoService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Webhook;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

class StripeWebhookController extends AbstractController
{
    #[Route('/webhook/stripe', name: 'app_stripe_webhook', methods: ['POST'])]
    public function index(
        Request $request, 
        UserRepository $userRepository, 
        PrestationRepository $prestationRepository, 
        SeanceRepository $seanceRepository,
        EntityManagerInterface $entityManager, 
        \App\Service\BookingMailerService $bookingMailer,
        DailyCoService $dailyCoService
    ): Response
    {
        $endpointSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');
        $event = null;

        // 1. Vérification de la sécurité Stripe
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return new Response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new Response('Invalid signature', 400);
        }

        // 2. Traitement du paiement validé
        if ($event->type === 'checkout.session.completed') {
            $sessionStripe = $event->data->object;
            
            $userId = $sessionStripe->metadata->user_id ?? null;
            $prestationId = $sessionStripe->metadata->prestation_id ?? null;
            $dateRendezVousStr = $sessionStripe->metadata->date_rendez_vous ?? null;

            if ($userId && $prestationId) {
                $user = $userRepository->find($userId);
                $prestation = $prestationRepository->find($prestationId);
                
                if ($user && $prestation) {
                    $dateRendezVous = $dateRendezVousStr ? new \DateTime($dateRendezVousStr) : null;

                    // Idempotence : Vérifier si la séance existe déjà
                    if ($dateRendezVous) {
                        $seanceExistante = $seanceRepository->findOneBy([
                            'user' => $user,
                            'prestation' => $prestation,
                            'dateRendezVous' => $dateRendezVous
                        ]);

                        if ($seanceExistante) {
                            return new Response('Webhook déjà traité', 200);
                        }
                    }

                    // --- 1ÈRE SÉANCE : CRÉATION & VISIO ---
                    $premiereSeance = new Seance();
                    $premiereSeance->setUser($user);
                    $premiereSeance->setPrestation($prestation);
                    $premiereSeance->setNumero(1);
                    $premiereSeance->setDuree($prestation->getDuree() ?? 60);
                    $premiereSeance->setDateRendezVous($dateRendezVous);
                    $premiereSeance->setStatut('En attente de validation');

                    // GÉNÉRATION ET ENREGISTREMENT DU LIEN DAILY.CO
                    if ($dateRendezVous) {
                        $lienVisio = $dailyCoService->createRoom($dateRendezVous);
                        if ($lienVisio) {
                            $premiereSeance->setLienVisio($lienVisio);
                        }
                    }
                    
                    $entityManager->persist($premiereSeance);

                    // --- SÉANCES SUIVANTES ---
                    $nombreTotalSeances = $prestation->getNombreSeances() ?? 1;
                    for ($i = 2; $i <= $nombreTotalSeances; $i++) {
                        $seanceSuivante = new Seance();
                        $seanceSuivante->setUser($user);
                        $seanceSuivante->setPrestation($prestation);
                        $seanceSuivante->setNumero($i);
                        $seanceSuivante->setDuree($prestation->getDuree() ?? 60);
                        $seanceSuivante->setDateRendezVous(null); 
                        $seanceSuivante->setStatut('Non planifiée');
                        
                        $entityManager->persist($seanceSuivante);
                    }

                    $entityManager->flush();

                    // --- ENVOI DES MAILS ---
                    $bookingMailer->sendPendingBookingToClient($premiereSeance);
                    $bookingMailer->sendNewBookingPaidToAdmin($premiereSeance, $prestation->getPrix());
                }
            }
        }

        return new Response('Webhook traité avec succès', 200);
    }
}