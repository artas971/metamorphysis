<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PrestationRepository;
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
            // C'EST ICI LA MAGIE : On attache nos données au billet Stripe pour le Webhook
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
            'success_url' => $this->generateUrl('app_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($checkout_session->url, 303);
    }

    #[Route('/commande/succes', name: 'app_stripe_success')]
    public function success(Request $request): Response
    {
        // La page de succès ne fait plus rien en base de données, elle est juste visuelle !
        // C'est le Webhook (invisible) qui a fait tout le travail.
        
        $request->getSession()->remove('reservation_en_cours');
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