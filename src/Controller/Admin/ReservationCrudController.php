<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class ReservationCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;
    private MailerInterface $mailer;

    // On injecte le générateur d'URL d'EasyAdmin et le Mailer
    public function __construct(AdminUrlGenerator $adminUrlGenerator, MailerInterface $mailer)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->mailer = $mailer;
    }

    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    // On ajoute notre bouton personnalisé
public function configureActions(Actions $actions): Actions
    {
        $confirmer = Action::new('confirmer', 'Confirmer le RDV', 'fas fa-check')
            ->linkToCrudAction('confirmerReservation')
            // ON CORRIGE LE DESIGN ICI : Texte vert et en gras, sans fond blanc/vert buggé
            ->addCssClass('text-success fw-bold') 
            ->displayIf(static function (Reservation $reservation) {
                return $reservation->getStatut() === 'En attente';
            });

        return $actions
            ->add(Crud::PAGE_INDEX, $confirmer)
            ->add(Crud::PAGE_DETAIL, $confirmer);
    }
    // L'action qui s'exécute quand on clique sur le bouton
    #[AdminRoute(path: '/confirmer-reservation', name: 'admin_reservation_confirmer')]
    public function confirmerReservation(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        /** @var Reservation $reservation */
        $reservation = $context->getEntity()->getInstance();

        // 1. On change le statut en base de données
        $reservation->setStatut('Confirmé');
        $entityManager->flush();

        // 2. On envoie l'e-mail de confirmation au client
        $email = (new TemplatedEmail())
            ->from('contact@metamorphysis.com')
            ->to($reservation->getUser()->getEmail())
            ->subject('Confirmation de votre rendez-vous - Metamorphysis')
            ->htmlTemplate('emails/confirmation_reservation.html.twig')
            ->context([
                'reservation' => $reservation,
                'client' => $reservation->getUser(),
                'prestation' => $reservation->getPrestation()
            ]);

        $this->mailer->send($email);

        // 3. Message de succès pour l'administrateur
        $this->addFlash('success', 'La réservation a été confirmée et le client a été prévenu par e-mail.');

        // 4. On redirige vers la liste des réservations
        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureFields(string $pageName): iterable
        {
            return [
                IdField::new('id')->hideOnForm(),
                
                // On affiche le client et la prestation
                AssociationField::new('user', 'Client')->hideOnForm(), // On cache sur le form pour éviter de réattribuer le RDV à un autre client par erreur
                AssociationField::new('prestation', 'Prestation'),
                
                DateTimeField::new('dateRendezVous', 'Date du RDV'),
                
                // LA MAGIE EST ICI : Le champ devient une liste déroulante stricte
                ChoiceField::new('statut', 'Statut')->setChoices([
                    'En attente' => 'En attente',
                    'Confirmé' => 'Confirmé',
                    'Annulé' => 'Annulé'
                ]),
            ];
        }
}