<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Entity\User; // <-- Ajout de l'entité User pour rassurer l'éditeur
use App\Repository\PrestationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class StripeController extends AbstractController
{
    #[Route('/commande/paiement', name: 'app_stripe_checkout')]
    public function checkout(Request $request, PrestationRepository $prestationRepository): Response
    {
        // 1. On récupère les infos temporaires
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

        // 2. Initialisation de Stripe avec la clé secrète du fichier .env
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        // Astuce pour l'éditeur de code : on lui précise que getUser() est bien notre entité User
        /** @var User $user */
        $user = $this->getUser();

        // 3. Création de la page de paiement Stripe Checkout
        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'customer_email' => $user->getEmail(), // L'erreur Intelephense va disparaître ici
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $prestation->getNom(),
                        'description' => 'Accompagnement - Metamorphysis',
                    ],
                    // Stripe prend les montants en centimes (donc on multiplie par 100)
                    'unit_amount' => $prestation->getPrix() * 100, 
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        // 4. Redirection de l'utilisateur vers la page de paiement sécurisée
        return $this->redirect($checkout_session->url, 303);
    }

    #[Route('/commande/succes', name: 'app_stripe_success')]
    public function success(Request $request, PrestationRepository $prestationRepository, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $sessionSymfony = $request->getSession();
        $reservationData = $sessionSymfony->get('reservation_en_cours');

        if (!$reservationData) {
            return $this->redirectToRoute('app_account');
        }

        $prestation = $prestationRepository->find($reservationData['prestation_id']);
        
        /** @var User $user */
        $user = $this->getUser();
        
        $dateRendezVous = new \DateTime($reservationData['date_rendez_vous']);

        // 1. Sauvegarde : Création de la première séance
        $premiereSeance = new Seance();
        $premiereSeance->setUser($user);
        $premiereSeance->setPrestation($prestation);
        $premiereSeance->setNumero(1);
        $premiereSeance->setDuree($prestation->getDuree() ?? 60);
        $premiereSeance->setDateRendezVous($dateRendezVous);
        $premiereSeance->setStatut('En attente de validation');

        $entityManager->persist($premiereSeance);

        // 2. Sauvegarde : Génération des séances suivantes du forfait
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

        // 3. Envoi du mail à l'administrateur
        $email = (new TemplatedEmail())
            ->from('noreply@metamorphysis.com')
            ->to('admin@metamorphysis.com')
            ->subject('Nouvelle réservation payée : ' . $prestation->getNom())
            ->htmlTemplate('emails/nouvelle_reservation.html.twig')
            ->context([
                'seance' => $premiereSeance,
                'client' => $user,
                'prestation' => $prestation
            ]);

        $mailer->send($email);

        // 4. On vide la session temporaire
        $sessionSymfony->remove('reservation_en_cours');

        // Modification avec le texte plus doux
        $this->addFlash('success', 'Merci pour votre confiance. Votre parcours d\'accompagnement est officiellement réservé.');

        return $this->redirectToRoute('app_account');
    }

    #[Route('/commande/erreur', name: 'app_stripe_cancel')]
    public function cancel(Request $request): Response
    {
        // Si le client clique sur "Retour" sur la page de paiement
        $this->addFlash('warning', 'Le paiement a été annulé. Vous n\'avez pas été débité. Vous pouvez réessayer quand vous le souhaitez.');
        
        return $this->redirectToRoute('app_account');
    }
}