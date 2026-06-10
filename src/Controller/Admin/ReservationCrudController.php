<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class ReservationCrudController extends AbstractCrudController
{
    private MailerInterface $mailer;

    // On injecte le service de mail de Symfony directement dans le constructeur
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user', 'Client')->hideOnForm(),
            AssociationField::new('prestation', 'Soin réservé')->hideOnForm(),
            DateTimeField::new('dateRendezVous', 'Date et Heure du RDV')->setFormat('dd/MM/yyyy HH:mm'),
            ChoiceField::new('statut', 'Statut de la demande')
                ->setChoices([
                    'En attente' => 'En attente',
                    'Confirmé' => 'Confirmé',
                    'Annulé' => 'Annulé',
                ]),
        ];
    }

    // Cette méthode se déclenche automatiquement quand l'admin clique sur "Enregistrer les modifications"
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // 1. On laisse EasyAdmin faire la mise à jour classique en base de données
        parent::updateEntity($entityManager, $entityInstance);

        // 2. Si l'entité est bien une réservation, on envoie le mail de confirmation ou d'annulation au client
        if ($entityInstance instanceof Reservation) {
            $clientEmail = $entityInstance->getUser()->getEmail();
            
            $email = (new TemplatedEmail())
                ->from('noreply@metamorphysis.com')
                ->to($clientEmail)
                ->subject('Mise à jour de votre réservation - Metamorphysis')
                ->htmlTemplate('emails/statut_reservation.html.twig')
                ->context([
                    'reservation' => $entityInstance,
                    'client' => $entityInstance->getUser(),
                    'statut' => $entityInstance->getStatut()
                ]);

            $this->mailer->send($email);
        }
    }
}