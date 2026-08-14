<?php

namespace App\EventSubscriber;

use App\Entity\Reservation;
use App\Entity\Seance;
use App\Service\PdfService;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class ReservationConfirmedSubscriber implements EventSubscriberInterface
{
    private $pdfService;
    private $mailer;
    private $twig;

    public function __construct(PdfService $pdfService, MailerInterface $mailer, Environment $twig)
    {
        $this->pdfService = $pdfService;
        $this->mailer = $mailer;
        $this->twig = $twig; 
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityUpdatedEvent::class => ['onReservationConfirmed'],
        ];
    }

    public function onReservationConfirmed(AfterEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        // 1. On vérifie que c'est une Séance ou une Réservation qui passe en "Confirmé"
        if (!($entity instanceof Reservation || $entity instanceof Seance) || $entity->getStatut() !== 'Confirmé') {
            return; 
        }

        if (!$entity->getUser() || !$entity->getUser()->getEmail()) {
            return;
        }

        try {
            // 2. On génère le HTML du PDF
            $htmlPdf = $this->twig->render('pdf/facture.html.twig', [
                'reservation' => $entity
            ]);

            // 3. On transforme le HTML en PDF binaire
            $pdfContent = $this->pdfService->generateBinaryPdf($htmlPdf);

            // 4. On génère le corps visuel de l'email (Le design Noir & Or)
            $emailBody = $this->twig->render('emails/reservation_confirm.html.twig', [
                'reservation' => $entity
            ]);

            // 5. Génération du nom de fichier dynamique
            $numeroFacture = 'FAC-' . date('Ymd') . '-' . $entity->getId();
            $nomClient = preg_replace('/[^A-Za-z0-9\-]/', '_', $entity->getUser()->getNom() ?? 'Client');
            $nomFichierPdf = 'Facture_Metamorphysis_' . $numeroFacture . '_' . strtoupper($nomClient) . '.pdf';

            // 6. Assemblage et envoi de l'email final avec la pièce jointe
            $email = (new Email())
                ->from('contact@metamorphysis.com')
                ->to($entity->getUser()->getEmail())
                ->subject('METAMORPHYSIS - Confirmation de votre rendez-vous')
                ->html($emailBody) 
                ->attach($pdfContent, $nomFichierPdf, 'application/pdf');

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Empêche un crash d'administration si le serveur SMTP est indisponible
        }
    }
}