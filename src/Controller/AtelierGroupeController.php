<?php

namespace App\Controller;

use App\Entity\InscriptionGroupe;
use App\Entity\SessionGroupe;
use App\Repository\SessionGroupeRepository;
use App\Service\DailyCoService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class AtelierGroupeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private DailyCoService $dailyCoService,
        private ?MailerInterface $mailer = null
    ) {}

    /**
     * Initialise un SetupIntent Stripe (0,00 €) pour enregistrer l'empreinte bancaire
     */
    #[Route('/atelier-groupe/setup-intent', name: 'app_atelier_setup_intent', methods: ['POST'])]
    public function createSetupIntent(Request $request): JsonResponse
    {
        $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? null;
        if (!$stripeSecret) {
            return new JsonResponse(['error' => 'Configuration Stripe manquante.'], 500);
        }

        Stripe::setApiKey($stripeSecret);

        try {
            $setupIntent = SetupIntent::create([
                'payment_method_types' => ['card'],
                'usage' => 'off_session',
            ]);

            return new JsonResponse([
                'clientSecret' => $setupIntent->client_secret,
                'publicKey' => $_ENV['STRIPE_PUBLIC_KEY'] ?? ''
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Valide la pré-réservation et enregistre le participant
     */
    #[Route('/atelier-groupe/inscrire', name: 'app_atelier_inscrire', methods: ['POST'])]
    public function inscrire(Request $request, SessionGroupeRepository $sessionRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Données invalides.'], 400);
        }

        $sessionId = $data['session_id'] ?? null;
        $nom = trim($data['nom'] ?? '');
        $prenom = trim($data['prenom'] ?? '');
        $email = trim($data['email'] ?? '');
        $telephone = trim($data['telephone'] ?? '');
        $paymentMethodId = $data['payment_method_id'] ?? null;

        if (!$sessionId || !$nom || !$prenom || !$email || !$paymentMethodId) {
            return new JsonResponse(['error' => 'Tous les champs obligatoires doivent être renseignés.'], 400);
        }

        $session = $sessionRepo->find((int)$sessionId);
        if (!$session) {
            return new JsonResponse(['error' => 'Session d\'atelier introuvable.'], 404);
        }

        if ($session->isComplet()) {
            return new JsonResponse(['error' => 'Désolé, cette session a atteint sa capacité maximale.'], 400);
        }

        // Vérifier si l'utilisateur n'est pas déjà inscrit
        foreach ($session->getInscriptions() as $existInsc) {
            if ($existInsc->getEmail() === $email && in_array($existInsc->getStatutPaiement(), ['Empreinte validée', 'Payé'])) {
                return new JsonResponse(['error' => 'Vous êtes déjà pré-inscrit à cette session.'], 400);
            }
        }

        // Créer un Customer Stripe et attacher la PaymentMethod
        $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? '';
        Stripe::setApiKey($stripeSecret);

        $customerId = null;
        try {
            $customer = Customer::create([
                'name' => $prenom . ' ' . $nom,
                'email' => $email,
                'phone' => $telephone,
            ]);
            $customerId = $customer->id;

            $pm = PaymentMethod::retrieve($paymentMethodId);
            $pm->attach(['customer' => $customerId]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Impossible de lier le moyen de paiement : ' . $e->getMessage()], 400);
        }

        // Enregistrer l'inscription
        $inscription = new InscriptionGroupe();
        $inscription->setSessionGroupe($session);
        $inscription->setUser($this->getUser());
        $inscription->setNom($nom);
        $inscription->setPrenom($prenom);
        $inscription->setEmail($email);
        $inscription->setTelephone($telephone);
        $inscription->setStripeCustomerId($customerId);
        $inscription->setStripePaymentMethodId($paymentMethodId);
        $inscription->setMontant($session->getPrestation()->getPrix() ?: 30.0);
        $inscription->setStatutPaiement('Empreinte validée');

        $session->addInscription($inscription);
        $this->em->persist($inscription);
        $this->em->flush();

        // VÉRIFICATION DU QUORUM (Seuil minimum atteint !)
        $quorumAtteint = false;
        if ($session->isQuorumAtteint() && $session->getStatut() === "En cours d'inscriptions") {
            $quorumAtteint = true;
            $this->declencherQuorum($session);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Pré-réservation enregistrée avec succès !',
            'quorumAtteint' => $quorumAtteint,
            'totalInscrits' => $session->getNombreInscrits(),
            'seuilMinimum' => $session->getSeuilMinimum(),
            'capaciteMax' => $session->getCapaciteMaximale(),
            'statutSession' => $session->getStatut(),
            'lienVisio' => $session->getLienVisio()
        ]);
    }

    /**
     * Déclenche le prélèvement simultané des 30 € et génère la salle Daily.co
     */
    private function declencherQuorum(SessionGroupe $session): void
    {
        $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? '';
        Stripe::setApiKey($stripeSecret);

        // 1. Génération de la salle Daily.co
        if (!$session->getLienVisio()) {
            $lien = $this->dailyCoService->createRoom($session->getDateDebut());
            if ($lien) {
                $session->setLienVisio($lien);
            }
        }

        // 2. Passage de la session en 'Confirmé'
        $session->setStatut('Confirmé');

        // 3. Débit simultané de chaque participant avec son empreinte
        foreach ($session->getInscriptions() as $insc) {
            if ($insc->getStatutPaiement() === 'Empreinte validée' && $insc->getStripePaymentMethodId()) {
                try {
                    $amountInCents = (int) round(($insc->getMontant() ?: 30.0) * 100);
                    $paymentIntent = PaymentIntent::create([
                        'amount' => $amountInCents,
                        'currency' => 'eur',
                        'customer' => $insc->getStripeCustomerId(),
                        'payment_method' => $insc->getStripePaymentMethodId(),
                        'off_session' => true,
                        'confirm' => true,
                        'description' => sprintf('Atelier de groupe : %s (%s)', 
                            $session->getPrestation()->getNom(), 
                            $session->getDateDebut()->format('d/m/Y H:i')
                        ),
                        'metadata' => [
                            'session_groupe_id' => $session->getId(),
                            'inscription_id' => $insc->getId(),
                            'email' => $insc->getEmail(),
                        ]
                    ]);

                    if ($paymentIntent->status === 'succeeded') {
                        $insc->setStatutPaiement('Payé');
                        $insc->setStripePaymentIntentId($paymentIntent->id);
                        $this->envoyerEmailConfirmation($insc, $session);
                    } else {
                        $insc->setStatutPaiement('Échec paiement');
                    }
                } catch (\Exception $e) {
                    $insc->setStatutPaiement('Échec paiement');
                }
            }
        }

        $this->em->flush();
    }

    private function envoyerEmailConfirmation(InscriptionGroupe $insc, SessionGroupe $session): void
    {
        if (!$this->mailer) return;

        try {
            $email = (new Email())
                ->from('contact@metamorphysis.fr')
                ->to($insc->getEmail())
                ->subject('🌿 Atelier Confirmé ! Votre accès Visio - Metamorphysis')
                ->html(sprintf('
                    <h2>Félicitations %s, votre atelier est confirmé !</h2>
                    <p>Le seuil des participants a été atteint. Votre paiement de 30,00 € a été validé.</p>
                    <p><strong>Date de la séance :</strong> %s</p>
                    <p><a href="%s" style="display:inline-block;padding:12px 20px;background:#B89A63;color:#fff;text-decoration:none;font-weight:bold;border-radius:4px;">Rejoindre la salle de visio</a></p>
                    <p>À très bientôt,<br>Louisa Chouihi - Metamorphysis</p>
                ', 
                htmlspecialchars($insc->getPrenom()),
                $session->getDateDebut()->format('d/m/Y à H:i'),
                $session->getLienVisio() ?: '#'
                ));

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log mail silencieux
        }
    }
}
