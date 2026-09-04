<?php

namespace App\Service;

use App\Entity\InscriptionGroupe;
use App\Entity\SessionGroupe;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class GroupeMailerService
{
    public const ADMIN_EMAIL = 'Metamorphysisconsulting@gmail.com';
    public const SENDER_EMAIL = 'contact@metamorphysis.com';
    public const SENDER_NAME = 'Metamorphysis';

    public function __construct(
        private MailerInterface $mailer,
        private ?LoggerInterface $logger = null
    ) {}

    /**
     * E-mail d'invitation envoyé à chaque membre lors de la planification d'une séance de groupe
     */
    public function sendNouvelleSeance(InscriptionGroupe $inscription): bool
    {
        $session = $inscription->getSessionGroupe();
        if (!$session || !$inscription->getEmail()) {
            return false;
        }

        try {
            $email = (new TemplatedEmail())
                ->from(new Address(self::SENDER_EMAIL, self::SENDER_NAME))
                ->to(new Address($inscription->getEmail(), $inscription->getNomComplet()))
                ->subject('🌿 Prochaine séance d\'Accompagnement en Groupe — Metamorphysis')
                ->htmlTemplate('emails/groupe_nouvelle_seance.html.twig')
                ->context([
                    'inscription' => $inscription,
                    'session' => $session,
                    'groupe' => $session->getGroupe(),
                    'prestation' => $session->getPrestation(),
                ]);

            $this->mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            $this->logger?->error('Erreur envoi email nouvelle seance groupe: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * E-mail notifiant le client de l'annulation de la séance et de la libération sans frais de son empreinte
     */
    public function sendSeanceAnnulee(InscriptionGroupe $inscription): bool
    {
        $session = $inscription->getSessionGroupe();
        if (!$session || !$inscription->getEmail()) {
            return false;
        }

        try {
            $email = (new TemplatedEmail())
                ->from(new Address(self::SENDER_EMAIL, self::SENDER_NAME))
                ->to(new Address($inscription->getEmail(), $inscription->getNomComplet()))
                ->subject('Information concernant votre séance d\'Accompagnement en Groupe — Metamorphysis')
                ->htmlTemplate('emails/groupe_seance_annulee.html.twig')
                ->context([
                    'inscription' => $inscription,
                    'session' => $session,
                    'groupe' => $session->getGroupe(),
                ]);

            $this->mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            $this->logger?->error('Erreur envoi email annulation seance groupe: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * E-mail confirmant la séance, le débit des 30 € et transmettant le lien Visio
     */
    public function sendSeanceValidee(InscriptionGroupe $inscription): bool
    {
        $session = $inscription->getSessionGroupe();
        if (!$session || !$inscription->getEmail()) {
            return false;
        }

        try {
            $email = (new TemplatedEmail())
                ->from(new Address(self::SENDER_EMAIL, self::SENDER_NAME))
                ->to(new Address($inscription->getEmail(), $inscription->getNomComplet()))
                ->subject('🌿 Votre séance d\'Accompagnement en Groupe est confirmée ! — Metamorphysis')
                ->htmlTemplate('emails/groupe_seance_validee.html.twig')
                ->context([
                    'inscription' => $inscription,
                    'session' => $session,
                    'groupe' => $session->getGroupe(),
                    'lienVisio' => $session->getLienVisio(),
                ]);

            $this->mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            $this->logger?->error('Erreur envoi email validation seance groupe: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Alerte transmise à Louisa lorsqu'un participant confirme, décline ou transmet un message
     */
    public function sendNotificationTherapeute(InscriptionGroupe $inscription, string $actionType): bool
    {
        $session = $inscription->getSessionGroupe();
        if (!$session) {
            return false;
        }

        try {
            $email = (new TemplatedEmail())
                ->from(new Address(self::SENDER_EMAIL, self::SENDER_NAME))
                ->to(new Address(self::ADMIN_EMAIL, 'Louisa Chouihi'))
                ->subject(sprintf('[Accompagnement en Groupe] %s - %s', $actionType, $inscription->getNomComplet()))
                ->htmlTemplate('emails/groupe_notification_therapeute.html.twig')
                ->context([
                    'inscription' => $inscription,
                    'session' => $session,
                    'groupe' => $session->getGroupe(),
                    'actionType' => $actionType,
                ]);

            $this->mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            $this->logger?->error('Erreur envoi email notification therapeute: ' . $e->getMessage());
            return false;
        }
    }
}
