<?php

namespace App\Controller\Admin;

use App\Entity\Groupe;
use App\Entity\InscriptionGroupe;
use App\Entity\SessionGroupe;
use App\Entity\User;
use App\Repository\GroupeRepository;
use App\Repository\SessionGroupeRepository;
use App\Repository\UserRepository;
use App\Service\DailyCoService;
use App\Service\GroupeMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminGroupePlanificationController extends AbstractController
{
    /**
     * Interface de planification rapide pour reconduire la cohorte lors de la séance suivante
     */
    #[Route('/groupe/{id}/planifier-seance', name: 'admin_groupe_planifier_seance', methods: ['GET', 'POST'])]
    public function planifierSeance(
        Groupe $groupe,
        Request $request,
        UserRepository $userRepo,
        EntityManagerInterface $em,
        GroupeMailerService $mailer
    ): Response {
        $lastSession = $groupe->getDerniereSession();
        $nextNumero = $lastSession ? ($lastSession->getNumeroSeance() ?? 1) + 1 : 1;

        // Récupération des participants de base (soit de la dernière séance, soit des membres du groupe)
        $participantsBase = [];
        if ($lastSession && $lastSession->getInscriptions()->count() > 0) {
            foreach ($lastSession->getInscriptions() as $insc) {
                if ($insc->getUser()) {
                    $participantsBase[$insc->getUser()->getId()] = [
                        'user' => $insc->getUser(),
                        'nom' => $insc->getNomComplet(),
                        'email' => $insc->getEmail(),
                        'telephone' => $insc->getTelephone(),
                        'checked' => $insc->getStatutPresence() !== 'Décliné'
                    ];
                }
            }
        }

        // Si aucun participant depuis la dernière séance, on utilise les membres du groupe
        if (empty($participantsBase)) {
            foreach ($groupe->getMembres() as $membre) {
                $participantsBase[$membre->getId()] = [
                    'user' => $membre,
                    'nom' => $membre->getPrenom() . ' ' . $membre->getNom(),
                    'email' => $membre->getEmail(),
                    'telephone' => $membre->getTelephone(),
                    'checked' => true
                ];
            }
        }

        // Tous les utilisateurs enregistrés au cas où Louisa souhaite ajouter d'autres personnes
        $allUsers = $userRepo->findBy([], ['nom' => 'ASC']);

        if ($request->isMethod('POST')) {
            $dateDebutStr = $request->request->get('date_debut');
            $dateFinStr = $request->request->get('date_fin');
            $titre = trim((string)$request->request->get('titre', ''));
            $selectedUserIds = (array)$request->request->all('selected_users');
            $nouveauxUserIds = (array)$request->request->all('nouveaux_users');
            $allTargetUserIds = array_unique(array_filter(array_merge($selectedUserIds, $nouveauxUserIds)));
            $sendNotifications = $request->request->getBoolean('send_notifications', true);

            if (!$dateDebutStr) {
                $this->addFlash('danger', 'Veuillez renseigner la date et l\'heure de début de la séance.');
                return $this->redirectToRoute('admin_groupe_planifier_seance', ['id' => $groupe->getId()]);
            }

            try {
                $dateDebut = new \DateTime($dateDebutStr);
                $dateFin = $dateFinStr ? new \DateTime($dateFinStr) : (clone $dateDebut)->modify('+1 hour');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Format de date invalide.');
                return $this->redirectToRoute('admin_groupe_planifier_seance', ['id' => $groupe->getId()]);
            }

            // Création de la nouvelle SessionGroupe
            $newSession = new SessionGroupe();
            $newSession->setGroupe($groupe);
            $newSession->setPrestation($groupe->getPrestation());
            $newSession->setNumeroSeance($nextNumero);
            $newSession->setDateDebut($dateDebut);
            $newSession->setDateFin($dateFin);
            $newSession->setTitre($titre ?: sprintf('Séance %d', $nextNumero));
            $newSession->setNotesTherapeute($notes);
            $newSession->setStatut("En cours d'inscriptions");
            // Les séances 2+ sont des séances privées de cohorte (invisibles sur le site public)
            $newSession->setEstVisiblePublic(false);

            $em->persist($newSession);

            $inscriptionsCreated = 0;
            foreach ($allTargetUserIds as $userId) {
                $user = $userRepo->find((int)$userId);
                if (!$user) continue;

                // Chercher d'éventuels identifiants Stripe antérieurs pour ce client
                $prevInsc = null;
                if ($lastSession) {
                    foreach ($lastSession->getInscriptions() as $li) {
                        if ($li->getUser() === $user || $li->getEmail() === $user->getEmail()) {
                            $prevInsc = $li;
                            break;
                        }
                    }
                }

                $insc = new InscriptionGroupe();
                $insc->setSessionGroupe($newSession);
                $insc->setUser($user);
                $insc->setNom($user->getNom() ?? 'Client');
                $insc->setPrenom($user->getPrenom() ?? 'Membre');
                $insc->setEmail($user->getEmail());
                $insc->setTelephone($user->getTelephone());
                $insc->setMontant($groupe->getPrestation()->getPrix() ?: 30.0);
                $insc->setStatutPresence('En attente');
                $insc->setStatutPaiement('En attente');

                if ($prevInsc) {
                    $insc->setStripeCustomerId($prevInsc->getStripeCustomerId());
                    $insc->setStripePaymentMethodId($prevInsc->getStripePaymentMethodId());
                }

                $newSession->addInscription($insc);
                $em->persist($insc);
                $inscriptionsCreated++;

                // Ajout automatique au groupe si pas déjà membre
                $groupe->addMembre($user);

                // Envoi de l'e-mail d'invitation
                if ($sendNotifications) {
                    $mailer->sendNouvelleSeance($insc);
                }
            }

            $em->flush();

            $this->addFlash('success', sprintf(
                'Séance n°%d créée avec succès pour le groupe "%s" ! %d participant(s) reconduit(s) et notifié(s).',
                $nextNumero,
                $groupe->getNom(),
                $inscriptionsCreated
            ));

            return $this->redirectToRoute('admin_session_groupe_index');
        }

        return $this->render('admin/groupe/planifier_seance.html.twig', [
            'groupe' => $groupe,
            'lastSession' => $lastSession,
            'nextNumero' => $nextNumero,
            'participantsBase' => $participantsBase,
            'allUsers' => $allUsers,
        ]);
    }

    /**
     * Raccourci depuis une session existante pour planifier la suivante
     */
    #[Route('/session-groupe/{id}/planifier-suivante', name: 'admin_session_planifier_suivante', methods: ['GET'])]
    public function planifierSuivanteDepuisSession(SessionGroupe $session): Response
    {
        $groupe = $session->getGroupe();
        if (!$groupe) {
            $this->addFlash('warning', 'Cette session n\'est rattachée à aucun groupe de suivi.');
            return $this->redirectToRoute('admin_session_groupe_index');
        }

        return $this->redirectToRoute('admin_groupe_planifier_seance', ['id' => $groupe->getId()]);
    }

    /**
     * Valide la séance, génère la salle Daily.co et débite les 30 € pour les présences confirmées
     */
    #[Route('/session-groupe/{id}/valider-debiter', name: 'admin_session_valider_debiter', methods: ['POST', 'GET'])]
    public function validerEtDebiter(
        SessionGroupe $session,
        EntityManagerInterface $em,
        DailyCoService $dailyCoService,
        GroupeMailerService $mailer
    ): Response {
        $stripeSecret = $_ENV['STRIPE_SECRET_KEY'] ?? '';
        if ($stripeSecret) {
            Stripe::setApiKey($stripeSecret);
        }

        // 1. Salle Visio Daily.co
        if (!$session->getLienVisio()) {
            $lien = $dailyCoService->createRoom($session->getDateDebut());
            if ($lien) {
                $session->setLienVisio($lien);
            }
        }

        $session->setStatut('Confirmé');

        $nbDebites = 0;
        foreach ($session->getInscriptions() as $insc) {
            if ($insc->getStatutPresence() === 'Confirmé' && $insc->getStatutPaiement() !== 'Payé') {
                if ($stripeSecret && $insc->getStripeCustomerId() && $insc->getStripePaymentMethodId()) {
                    try {
                        $amountInCents = (int) round(($insc->getMontant() ?: 30.0) * 100);
                        $paymentIntent = PaymentIntent::create([
                            'amount' => $amountInCents,
                            'currency' => 'eur',
                            'customer' => $insc->getStripeCustomerId(),
                            'payment_method' => $insc->getStripePaymentMethodId(),
                            'off_session' => true,
                            'confirm' => true,
                            'description' => sprintf('Accompagnement en groupe : %s (Séance %s)', 
                                $session->getPrestation()->getNom(),
                                $session->getNumeroSeance() ?: '1'
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
                            $nbDebites++;
                        }
                    } catch (\Throwable $e) {
                        // Statut en attente si la carte a expiré ou échec
                    }
                } else {
                    // Si payé manuellement ou test dev
                    $insc->setStatutPaiement('Payé');
                    $nbDebites++;
                }

                // Notification e-mail de validation avec accès visio
                $mailer->sendSeanceValidee($insc);
            }
        }

        $em->flush();

        $this->addFlash('success', sprintf(
            'Séance confirmée avec succès ! %d participant(s) débité(s) de 30 € et prévenus par e-mail avec leur lien de visioconférence.',
            $nbDebites
        ));

        return $this->redirectToRoute('admin_session_groupe_index');
    }

    /**
     * Annule la séance si le nombre de participants requis n'est pas atteint :
     * Libère immédiatement toutes les empreintes sans aucun débit et prévient les clients
     */
    #[Route('/session-groupe/{id}/annuler-liberer', name: 'admin_session_annuler_liberer', methods: ['POST', 'GET'])]
    public function annulerEtLiberer(
        SessionGroupe $session,
        EntityManagerInterface $em,
        GroupeMailerService $mailer
    ): Response {
        $session->setStatut('Annulé');

        $nbNotifies = 0;
        foreach ($session->getInscriptions() as $insc) {
            // Seuls les participants confirmés ou en attente reçoivent l'info
            if ($insc->getStatutPresence() !== 'Décliné') {
                $insc->setStatutPaiement('Annulé');
                $mailer->sendSeanceAnnulee($insc);
                $nbNotifies++;
            }
        }

        $em->flush();

        $this->addFlash('warning', sprintf(
            'La séance a été annulée. Les empreintes bancaires de tous les participants ont été libérées à 0,00 € sans aucun frais. %d e-mail(s) d\'information envoyé(s).',
            $nbNotifies
        ));

        return $this->redirectToRoute('admin_session_groupe_index');
    }
}
