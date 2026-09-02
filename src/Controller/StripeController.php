<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Entity\User;
use App\Repository\PrestationRepository;
use App\Repository\SeanceRepository;
use App\Repository\UserRepository;
use App\Service\DailyCoService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class StripeController extends AbstractController
{
    #[Route('/commande/paiement', name: 'app_stripe_checkout')]
    public function checkout(Request $request, PrestationRepository $prestationRepository): Response
    {
        $sessionSymfony = $request->getSession();
        $reservationData = $sessionSymfony->get('reservation_en_cours');

        if (!$reservationData) {
            $this->addFlash('danger', 'Votre session a expiré. Veuillez recommencer la réservation.');
            return $this->redirectToRoute('app_account');
        }

        $prestation = $prestationRepository->find($reservationData['prestation_id']);

        if (!$prestation) {
            return $this->redirectToRoute('app_account');
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        /** @var User $user */
        $user = $this->getUser();

        if ($user && $user->hasActivePrestation($prestation)) {
            $this->addFlash('warning', 'Vous suivez déjà un accompagnement en cours pour la prestation "' . $prestation->getNom() . '". Vous devez terminer vos séances actuelles avant de pouvoir reprendre ce même type de prestation.');
            return $this->redirectToRoute('app_account');
        }

        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'customer_email' => $user->getEmail(),
            'metadata' => [
                'user_id' => $user->getId(),
                'prestation_id' => $prestation->getId(),
                'date_rendez_vous' => $reservationData['date_rendez_vous']
            ],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $prestation->getNom(),
                        'description' => 'Accompagnement - Metamorphysis',
                    ],
                    'unit_amount' => $prestation->getPrix() * 100, 
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('app_stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($checkout_session->url, 303);
    }

    #[Route('/commande/succes', name: 'app_stripe_success')]
    public function success(
        Request $request,
        PrestationRepository $prestationRepository,
        SeanceRepository $seanceRepository,
        EntityManagerInterface $entityManager,
        DailyCoService $dailyCoService,
        \App\Service\BookingMailerService $bookingMailer
    ): Response {
        $sessionSymfony = $request->getSession();
        $reservationData = $sessionSymfony->get('reservation_en_cours');

        /** @var User|null $user */
        $user = $this->getUser();

        if ($reservationData && $user) {
            $prestation = $prestationRepository->find($reservationData['prestation_id'] ?? null);
            $dateRendezVousStr = $reservationData['date_rendez_vous'] ?? null;
            $dateRendezVous = $dateRendezVousStr ? new \DateTime($dateRendezVousStr) : null;

            if ($prestation) {
                // Idempotence : vérifier si la séance 1 existe déjà
                $seanceExistante = $seanceRepository->findOneBy([
                    'user' => $user,
                    'prestation' => $prestation,
                    'numero' => 1
                ]);

                if (!$seanceExistante) {
                    // Créer séance 1
                    $premiereSeance = new Seance();
                    $premiereSeance->setUser($user);
                    $premiereSeance->setPrestation($prestation);
                    $premiereSeance->setNumero(1);
                    $premiereSeance->setDuree($prestation->getDuree() ?? 60);
                    $premiereSeance->setDateRendezVous($dateRendezVous);
                    $premiereSeance->setStatut('En attente de validation');

                    if ($dateRendezVous) {
                        $lienVisio = $dailyCoService->createRoom($dateRendezVous);
                        if ($lienVisio) {
                            $premiereSeance->setLienVisio($lienVisio);
                        }
                    }
                    $entityManager->persist($premiereSeance);

                    // Créer séances 2..N
                    $totalSeances = $prestation->getNombreSeances() ?? 1;
                    for ($i = 2; $i <= $totalSeances; $i++) {
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

                    // 1. Email Client (Demande de RDV bien enregistrée, en attente de validation par Louisa)
                    $bookingMailer->sendPendingBookingToClient($premiereSeance);

                    // 2. Email Admin (Notification paiement validé & séance en attente)
                    $bookingMailer->sendNewBookingPaidToAdmin($premiereSeance, $prestation->getPrix());
                }
            }
        }

        $sessionSymfony->remove('reservation_en_cours');

        $this->addFlash('success', 'Merci pour votre confiance. Votre paiement a été validé et votre demande de rendez-vous a bien été prise en compte.');

        return $this->redirectToRoute('app_account');
    }

    #[Route('/commande/erreur', name: 'app_stripe_cancel')]
    public function cancel(Request $request): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé. Vous n\'avez pas été débité.');
        return $this->redirectToRoute('app_account');
    }
}