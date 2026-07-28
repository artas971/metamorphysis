<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Entity\User;
use App\Repository\PrestationRepository;
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
        EntityManagerInterface $em, 
        PrestationRepository $prestationRepository,
        DailyCoService $dailyCoService
    ): Response {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        
        $sessionSymfony = $request->getSession();
        $reservationData = $sessionSymfony->get('reservation_en_cours');
        $stripeSessionId = $request->query->get('session_id');

        $prestationId = null;
        $dateRendezVousStr = null;

        if ($reservationData) {
            $prestationId = $reservationData['prestation_id'] ?? null;
            $dateRendezVousStr = $reservationData['date_rendez_vous'] ?? null;
        } elseif ($stripeSessionId) {
            try {
                $stripeSession = Session::retrieve($stripeSessionId);
                if ($stripeSession && isset($stripeSession->metadata)) {
                    $prestationId = $stripeSession->metadata->prestation_id ?? null;
                    $dateRendezVousStr = $stripeSession->metadata->date_rendez_vous ?? null;
                }
            } catch (\Exception $e) {
                // S'il y a un souci de récupération Stripe, on continue
            }
        }

        // --- DUMP DE DÉBOGAGE #1 : VÉRIFICATION DES DONNÉES REÇUES ---
        // Décommente ce bloc si la page se redirige directement sans s'arrêter
        /*
        dd([
            'etape' => '1. Vérification entrée dans success',
            'prestation_id' => $prestationId,
            'date_rendez_vous_str' => $dateRendezVousStr,
            'source_session_symfony' => $reservationData,
            'source_stripe_session_id' => $stripeSessionId
        ]);
        */

        if ($prestationId) {
            $prestation = $prestationRepository->find($prestationId);
            $user = $this->getUser();

            if ($prestation && $user) {
                $nbSeances = $prestation->getNombreSeances() ?? 1;

                for ($i = 1; $i <= $nbSeances; $i++) {
                    $seance = new Seance();
                    $seance->setUser($user);
                    $seance->setPrestation($prestation);
                    $seance->setNumero($i);

                    if ($i === 1 && !empty($dateRendezVousStr)) {
                        $dateRdv = new \DateTime($dateRendezVousStr);
                        $seance->setDateRendezVous($dateRdv);
                        $seance->setStatut('En attente de validation');

                        // APPEL DU SERVICE DAILY.CO
                        $lienVisio = $dailyCoService->createRoom($dateRdv);
 
                        if ($lienVisio) {
                            $seance->setLienVisio($lienVisio);
                        }
                    } else {
                        $seance->setStatut('Non planifiée');
                    }

                    $em->persist($seance);
                }

                $em->flush();
            }

            $sessionSymfony->remove('reservation_en_cours');
        }

        $this->addFlash('success', 'Merci pour votre confiance. Votre parcours d\'accompagnement est officiellement réservé.');

        return $this->redirectToRoute('app_account');
    }

    #[Route('/commande/erreur', name: 'app_stripe_cancel')]
    public function cancel(Request $request): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé. Vous n\'avez pas été débité.');
        return $this->redirectToRoute('app_account');
    }
}