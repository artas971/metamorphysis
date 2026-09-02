<?php

namespace App\Service;

use App\Entity\Seance;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment;

class BookingMailerService
{
    public const ADMIN_EMAIL = 'Metamorphysisconsulting@gmail.com';
    public const SENDER_EMAIL = 'contact@metamorphysis.com';
    public const SENDER_NAME = 'Metamorphysis';

    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private PdfService $pdfService,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * 1. CLIENT : Email informant que la demande de RDV est bien enregistrée et en attente de validation par Louisa.
     */
    public function sendPendingBookingToClient(Seance $seance, bool $attachInvoice = false): bool
    {
        $client = $seance->getUser();
        if (!$client || !$client->getEmail()) {
            return false;
        }

        try {
            $pdfContent = null;
            $pdfFilename = null;

            if ($attachInvoice) {
                try {
                    $htmlPdf = $this->twig->render('pdf/facture.html.twig', [
                        'reservation' => $seance
                    ]);
                    $pdfContent = $this->pdfService->generateBinaryPdf($htmlPdf);
                    
                    $numeroFacture = 'FAC-' . date('Ymd') . '-' . $seance->getId();
                    $nomClientClean = preg_replace('/[^A-Za-z0-9\-]/', '_', $client->getNom() ?? 'Client');
                    $pdfFilename = 'Facture_Metamorphysis_' . $numeroFacture . '_' . strtoupper($nomClientClean) . '.pdf';
                } catch (\Throwable $pdfError) {
                    $this->logger?->warning('Impossible de générer le PDF de facture: ' . $pdfError->getMessage());
                }
            }

            $email = (new TemplatedEmail())
                ->from(new Address(self::SENDER_EMAIL, self::SENDER_NAME))
                ->to(new Address($client->getEmail(), $client->getPrenom() . ' ' . $client->getNom()))
                ->subject('Votre demande de rendez-vous a bien été prise en compte — Metamorphysis')
                ->htmlTemplate('emails/client_demande_en_attente.html.twig')
                ->context([
                    'seance' => $seance,
                    'client' => $client,
                    'prestation' => $seance->getPrestation(),
                    'hasInvoice' => ($pdfContent !== null),
                ]);

            if ($pdfContent && $pdfFilename) {
                $email->attach($pdfContent, $pdfFilename, 'application/pdf');
            }

            $this->mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            $this->logger?->error('Erreur envoi email client demande en attente: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 2. ADMIN : Notification à Louisa d'un paiement validé et d'une séance à valider.
     */
    public function sendNewBookingPaidToAdmin(Seance $seance, ?float $montant = null): bool
    {
        $client = $seance->getUser();
        if (!$client) {
            return false;
        }

        try {
            $email = (new TemplatedEmail())
                ->from(new Address(self::SENDER_EMAIL, self::SENDER_NAME))
                ->to(self::ADMIN_EMAIL)
                ->subject('🔔 Paiement validé & Nouvelle demande de RDV : ' . ($client->getPrenom() ?? '') . ' ' . ($client->getNom() ?? ''))
                ->htmlTemplate('emails/admin_nouvelle_reservation.html.twig')
                ->context([
                    'seance' => $seance,
                    'client' => $client,
                    'prestation' => $seance->getPrestation(),
                    'montant' => $montant ?? ($seance->getPrestation()?->getPrix()),
                ]);

            $this->mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            $this->logger?->error('Erreur envoi email admin nouveau paiement: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 3. CLIENT : Notification de confirmation officielle du rendez-vous (avec facture PDF jointe).
     */
    public function sendBookingConfirmedToClient(Seance $seance, ?string $pdfContent = null, ?string $pdfFilename = null): bool
    {
        $client = $seance->getUser();
        if (!$client || !$client->getEmail()) {
            return false;
        }

        try {
            // Génération de la facture PDF si non fournie
            if ($pdfContent === null) {
                try {
                    $htmlPdf = $this->twig->render('pdf/facture.html.twig', [
                        'reservation' => $seance
                    ]);
                    $pdfContent = $this->pdfService->generateBinaryPdf($htmlPdf);
                    
                    $numeroFacture = 'FAC-' . date('Ymd') . '-' . $seance->getId();
                    $nomClientClean = preg_replace('/[^A-Za-z0-9\-]/', '_', $client->getNom() ?? 'Client');
                    $pdfFilename = 'Facture_Metamorphysis_' . $numeroFacture . '_' . strtoupper($nomClientClean) . '.pdf';
                } catch (\Throwable $pdfError) {
                    $this->logger?->warning('Impossible de générer le PDF de facture: ' . $pdfError->getMessage());
                    $pdfContent = null;
                }
            }

            $email = (new TemplatedEmail())
                ->from(new Address(self::SENDER_EMAIL, self::SENDER_NAME))
                ->to(new Address($client->getEmail(), $client->getPrenom() . ' ' . $client->getNom()))
                ->subject('✨ Votre rendez-vous est confirmé ! — Metamorphysis')
                ->htmlTemplate('emails/client_seance_confirmee.html.twig')
                ->context([
                    'seance' => $seance,
                    'client' => $client,
                    'prestation' => $seance->getPrestation(),
                ]);

            if ($pdfContent && $pdfFilename) {
                $email->attach($pdfContent, $pdfFilename, 'application/pdf');
            }

            $this->mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            $this->logger?->error('Erreur envoi email client confirmation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 4. CLIENT : Rappel 48h avant la séance avec le lien de visioconférence Daily.co.
     */
    public function sendReminder48hToClient(Seance $seance): bool
    {
        $client = $seance->getUser();
        if (!$client || !$client->getEmail()) {
            return false;
        }

        try {
            $email = (new TemplatedEmail())
                ->from(new Address(self::SENDER_EMAIL, self::SENDER_NAME))
                ->to(new Address($client->getEmail(), $client->getPrenom() . ' ' . $client->getNom()))
                ->subject('🌿 Rappel : Votre séance Metamorphysis a lieu dans 48h')
                ->htmlTemplate('emails/client_rappel_48h.html.twig')
                ->context([
                    'seance' => $seance,
                    'client' => $client,
                    'prestation' => $seance->getPrestation(),
                ]);

            $this->mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            $this->logger?->error('Erreur envoi email rappel 48h: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 5. CLIENT : Confirmation d'annulation de séance.
     */
    public function sendCancellationToClient(Seance $seance, ?\DateTimeInterface $ancienneDate = null): bool
    {
        $client = $seance->getUser();
        if (!$client || !$client->getEmail()) {
            return false;
        }

        try {
            $email = (new TemplatedEmail())
                ->from(new Address(self::SENDER_EMAIL, self::SENDER_NAME))
                ->to(new Address($client->getEmail(), $client->getPrenom() . ' ' . $client->getNom()))
                ->subject('Annulation de votre séance — Metamorphysis')
                ->htmlTemplate('emails/client_annulation_seance.html.twig')
                ->context([
                    'seance' => $seance,
                    'client' => $client,
                    'prestation' => $seance->getPrestation(),
                    'ancienneDate' => $ancienneDate,
                ]);

            $this->mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            $this->logger?->error('Erreur envoi email client annulation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 6. ADMIN : Notification d'annulation par le client.
     */
    public function sendCancellationToAdmin(Seance $seance, ?\DateTimeInterface $ancienneDate = null): bool
    {
        $client = $seance->getUser();
        if (!$client) {
            return false;
        }

        try {
            $email = (new TemplatedEmail())
                ->from(new Address(self::SENDER_EMAIL, self::SENDER_NAME))
                ->to(self::ADMIN_EMAIL)
                ->subject('⚠️ Annulation Client : Séance ' . $seance->getNumero() . ' - ' . ($client->getPrenom() ?? '') . ' ' . ($client->getNom() ?? ''))
                ->htmlTemplate('emails/admin_annulation_seance.html.twig')
                ->context([
                    'seance' => $seance,
                    'client' => $client,
                    'prestation' => $seance->getPrestation(),
                    'ancienneDate' => $ancienneDate,
                ]);

            $this->mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            $this->logger?->error('Erreur envoi email admin annulation: ' . $e->getMessage());
            return false;
        }
    }
}
