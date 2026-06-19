<?php

namespace App\EventSubscriber;

use App\Entity\Reservation;
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

        // 1. On vérifie que c'est une Réservation qui passe en "Confirmé"
        if (!($entity instanceof Reservation) || $entity->getStatut() !== 'Confirmé') {
            return; 
        }

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

        // --- NOUVEAUTÉ : GÉNÉRATION DU NOM DE FICHIER DYNAMIQUE ---
        
        // On génère le numéro de facture (ex: FAC-20260619-2)
        $numeroFacture = 'FAC-' . date('Ymd') . '-' . $entity->getId();
        
        // On nettoie le nom du client pour éviter les espaces ou caractères spéciaux dans le nom du fichier
        $nomClient = preg_replace('/[^A-Za-z0-9\-]/', '_', $entity->getUser()->getNom());
        
        // Nom final : Facture_Metamorphysis_FAC-20260619-2_NOM.pdf
        $nomFichierPdf = 'Facture_Metamorphysis_' . $numeroFacture . '_' . strtoupper($nomClient) . '.pdf';

        // ----------------------------------------------------------

        // 5. On assemble et on envoie l'email final avec la pièce jointe correctement nommée
        $email = (new Email())
            ->from('contact@metamorphysis.com')
            ->to($entity->getUser()->getEmail())
            ->subject('METAMORPHYSIS - Confirmation de votre rendez-vous')
            ->html($emailBody) 
            ->attach($pdfContent, $nomFichierPdf, 'application/pdf');

        $this->mailer->send($email);
    }
}