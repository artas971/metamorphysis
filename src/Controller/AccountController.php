<?php

namespace App\Controller;

use App\Entity\InscriptionGroupe;
use App\Form\ChangePasswordType;
use App\Form\ProfileEditType;
use App\Repository\InscriptionGroupeRepository;
use App\Repository\SeanceRepository;
use App\Service\GroupeMailerService;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Customer;
use Stripe\PaymentMethod;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;

#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_account')]
    public function index(SeanceRepository $seanceRepository, InscriptionGroupeRepository $inscriptionGroupeRepo): Response
    {
        $user = $this->getUser();

        // On récupère toutes les séances de l'utilisateur, triées par Prestation puis par Numéro (1, 2, 3...)
        $seances = $seanceRepository->findBy(
            ['user' => $user],
            ['prestation' => 'ASC', 'numero' => 'ASC']
        );

        // On regroupe les séances par Prestation pour créer l'affichage "Parcours"
        $parcoursList = [];
        foreach ($seances as $seance) {
            $prestId = $seance->getPrestation()->getId();
            
            if (!isset($parcoursList[$prestId])) {
                $parcoursList[$prestId] = [
                    'prestation' => $seance->getPrestation(),
                    'seances' => [],
                    'total' => $seance->getPrestation()->getNombreSeances() ?? 1,
                    'planifiees' => 0
                ];
            }
            
            $parcoursList[$prestId]['seances'][] = $seance;
            
            // On compte combien de séances ont déjà une date
            if ($seance->getDateRendezVous() !== null) {
                $parcoursList[$prestId]['planifiees']++;
            }
        }

        // On récupère les inscriptions aux accompagnements en groupe
        $inscriptionsGroupe = $inscriptionGroupeRepo->createQueryBuilder('ig')
            ->join('ig.sessionGroupe', 'sg')
            ->where('ig.user = :user OR ig.email = :email')
            ->setParameter('user', $user)
            ->setParameter('email', $user->getEmail())
            ->orderBy('sg.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('account/index.html.twig', [
            'parcoursList' => $parcoursList,
            'inscriptionsGroupe' => $inscriptionsGroupe,
        ]);
    }

    #[Route('/mon-compte/modifier', name: 'app_account_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Vos informations personnelles ont été mises à jour.');
            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/mon-compte/mot-de-passe', name: 'app_password_change')]
    public function changePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }
        
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/mon-compte/facture/{id}', name: 'app_account_facture')]
    public function telechargerFacture(\App\Entity\Seance $seance, \App\Service\PdfService $pdfService, \Twig\Environment $twig): Response
    {
        $user = $this->getUser();
        if ($seance->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à consulter cette facture.');
        }

        $htmlPdf = $twig->render('pdf/facture.html.twig', [
            'reservation' => $seance
        ]);

        $pdfContent = $pdfService->generateBinaryPdf($htmlPdf);

        $numeroFacture = 'FAC-' . date('Ymd') . '-' . $seance->getId();
        $nomClient = preg_replace('/[^A-Za-z0-9\-]/', '_', $user->getNom() ?? 'Client');
        $nomFichierPdf = 'Facture_Metamorphysis_' . $numeroFacture . '_' . strtoupper($nomClient) . '.pdf';

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $nomFichierPdf . '"'
        ]);
    }

    #[Route('/mon-compte/groupe/{id}/confirmer-empreinte', name: 'app_account_groupe_confirmer', methods: ['POST'])]
    public function confirmerEmpreinteGroupe(
        int $id,
        Request $request,
        InscriptionGroupeRepository $repo,
        EntityManagerInterface $em,
        GroupeMailerService $mailer
    ): Response {
        $user = $this->getUser();
        $inscription = $repo->find($id);

        if (!$inscription || ($inscription->getUser() !== $user && $inscription->getEmail() !== $user->getEmail())) {
            throw $this->createAccessDeniedException('Inscription introuvable ou non autorisée.');
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $paymentMethodId = $request->request->get('payment_method_id') ?? ($data['payment_method_id'] ?? null);
        $message = $request->request->get('message') ?? ($data['message'] ?? null);

        if (!$inscription->getUser()) {
            $inscription->setUser($user);
        }

        // Si une méthode de paiement est transmise
        if ($paymentMethodId) {
            $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? '';
            if ($stripeSecret) {
                Stripe::setApiKey($stripeSecret);
                try {
                    $customerId = $inscription->getStripeCustomerId();
                    if (!$customerId) {
                        $customer = Customer::create([
                            'name' => $user->getPrenom() . ' ' . $user->getNom(),
                            'email' => $user->getEmail(),
                            'phone' => $user->getTelephone(),
                        ]);
                        $customerId = $customer->id;
                        $inscription->setStripeCustomerId($customerId);
                    }
                    $pm = PaymentMethod::retrieve($paymentMethodId);
                    $pm->attach(['customer' => $customerId]);
                    $inscription->setStripePaymentMethodId($paymentMethodId);
                } catch (\Throwable $e) {
                    if ($request->isXmlHttpRequest() || $request->headers->has('X-Requested-With')) {
                        return $this->json(['error' => 'Erreur Stripe : ' . $e->getMessage()], 400);
                    }
                    $this->addFlash('danger', 'Impossible d\'enregistrer la carte : ' . $e->getMessage());
                    return $this->redirectToRoute('app_account');
                }
            }
        }

        $inscription->setStatutPresence('Confirmé');
        $inscription->setStatutPaiement('Empreinte validée');
        $inscription->setDateReponse(new \DateTimeImmutable());

        if (!empty($message)) {
            $inscription->setMessageParticipant(trim($message));
        }

        $em->flush();

        // Notifier Louisa
        $mailer->sendNotificationTherapeute($inscription, 'Présence confirmée (Empreinte validée)');

        if ($request->isXmlHttpRequest() || str_contains($request->headers->get('Accept', ''), 'application/json')) {
            return $this->json([
                'success' => true,
                'message' => 'Votre présence a été confirmée ! Votre empreinte bancaire de 30 € est enregistrée (0,00 € débité aujourd\'hui).'
            ]);
        }

        $this->addFlash('success', 'Votre présence a été confirmée ! Votre empreinte bancaire de 30 € est enregistrée (0,00 € débité aujourd\'hui).');
        return $this->redirectToRoute('app_account');
    }

    #[Route('/mon-compte/groupe/{id}/decliner', name: 'app_account_groupe_decliner', methods: ['POST'])]
    public function declinerGroupe(
        int $id,
        Request $request,
        InscriptionGroupeRepository $repo,
        EntityManagerInterface $em,
        GroupeMailerService $mailer
    ): Response {
        $user = $this->getUser();
        $inscription = $repo->find($id);

        if (!$inscription || ($inscription->getUser() !== $user && $inscription->getEmail() !== $user->getEmail())) {
            throw $this->createAccessDeniedException('Inscription introuvable ou non autorisée.');
        }

        $message = $request->request->get('message');
        if (!empty($message)) {
            $inscription->setMessageParticipant(trim($message));
        }

        $inscription->setStatutPresence('Décliné');
        $inscription->setDateReponse(new \DateTimeImmutable());
        $em->flush();

        $mailer->sendNotificationTherapeute($inscription, 'Participation déclinée');

        $this->addFlash('info', 'Votre réponse a bien été prise en compte : votre participation est déclinée sans aucun frais.');
        return $this->redirectToRoute('app_account');
    }

    #[Route('/mon-compte/groupe/{id}/message', name: 'app_account_groupe_message', methods: ['POST'])]
    public function messageGroupe(
        int $id,
        Request $request,
        InscriptionGroupeRepository $repo,
        EntityManagerInterface $em,
        GroupeMailerService $mailer
    ): Response {
        $user = $this->getUser();
        $inscription = $repo->find($id);

        if (!$inscription || ($inscription->getUser() !== $user && $inscription->getEmail() !== $user->getEmail())) {
            throw $this->createAccessDeniedException('Inscription introuvable ou non autorisée.');
        }

        $message = trim((string)$request->request->get('message', ''));
        if (!empty($message)) {
            $inscription->setMessageParticipant($message);
            $inscription->setDateReponse(new \DateTimeImmutable());
            $em->flush();

            $mailer->sendNotificationTherapeute($inscription, 'Message du participant');
            $this->addFlash('success', 'Votre message a bien été transmis à Louisa.');
        }

        return $this->redirectToRoute('app_account');
    }

    #[Route('/mon-compte/groupe/facture/{id}', name: 'app_account_groupe_facture')]
    public function telechargerFactureGroupe(
        int $id,
        InscriptionGroupeRepository $repo,
        PdfService $pdfService,
        Environment $twig
    ): Response {
        $user = $this->getUser();
        $inscription = $repo->find($id);

        if (!$inscription || ($inscription->getUser() !== $user && $inscription->getEmail() !== $user->getEmail())) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à consulter cette facture.');
        }

        if (!in_array($inscription->getStatutPaiement(), ['Payé', 'Empreinte validée'])) {
            throw $this->createNotFoundException('La facture n\'est pas encore disponible.');
        }

        $htmlPdf = $twig->render('pdf/facture_groupe.html.twig', [
            'inscription' => $inscription,
            'session' => $inscription->getSessionGroupe(),
            'user' => $user
        ]);

        $pdfContent = $pdfService->generateBinaryPdf($htmlPdf);

        $numeroFacture = 'FAC-GRP-' . date('Ymd') . '-' . $inscription->getId();
        $nomClient = preg_replace('/[^A-Za-z0-9\-]/', '_', $user->getNom() ?? 'Client');
        $nomFichierPdf = 'Facture_Metamorphysis_' . $numeroFacture . '_' . strtoupper($nomClient) . '.pdf';

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $nomFichierPdf . '"'
        ]);
    }
}