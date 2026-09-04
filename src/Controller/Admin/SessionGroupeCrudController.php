<?php

namespace App\Controller\Admin;

use App\Entity\SessionGroupe;
use App\Service\DailyCoService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class SessionGroupeCrudController extends AbstractCrudController
{
    public function __construct(
        private DailyCoService $dailyCoService
    ) {}

    public static function getEntityFqcn(): string
    {
        return SessionGroupe::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular("Session d'Atelier de Groupe")
            ->setEntityLabelInPlural("Sessions d'Ateliers de Groupe")
            ->setDefaultSort(['dateDebut' => 'ASC'])
            ->setHelp('index', 'Planifiez ici les créneaux de vos ateliers de groupe. Dès que 5 personnes sont inscrites, la session est confirmée automatiquement.');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', '#')->hideOnForm();

        yield AssociationField::new('prestation', '💆 Atelier / Prestation')
            ->setRequired(true)
            ->setHelp('Sélectionnez la prestation collective concernée.');

        yield DateTimeField::new('dateDebut', '📅 Date & Heure de Début')
            ->setRequired(true)
            ->setFormat('dd/MM/yyyy HH:mm');

        yield DateTimeField::new('dateFin', '⏰ Date & Heure de Fin')
            ->setRequired(false)
            ->setFormat('dd/MM/yyyy HH:mm');

        yield ChoiceField::new('statut', 'Statut de la session')
            ->setChoices([
                "En cours d'inscriptions" => "En cours d'inscriptions",
                'Confirmé (Minimum de participants atteint)' => 'Confirmé',
                'Annulé' => 'Annulé',
                'Effectué' => 'Effectué',
            ])
            ->renderAsBadges([
                "En cours d'inscriptions" => 'warning',
                'Confirmé' => 'success',
                'Annulé' => 'danger',
                'Effectué' => 'secondary',
            ]);

        yield TextField::new('jaugeInscriptions', '👥 Progression Jauge')
            ->onlyOnIndex()
            ->formatValue(function ($value, SessionGroupe $entity) {
                return sprintf('<strong>%d / %d min</strong> (max %d)', 
                    $entity->getNombreInscrits(), 
                    $entity->getSeuilMinimum(), 
                    $entity->getCapaciteMaximale()
                );
            });

        yield UrlField::new('lienVisio', '📹 Lien Salle Visio (Daily.co)')
            ->setHelp('Le lien est généré automatiquement dès que le seuil de 5 inscrits est atteint.');

        yield AssociationField::new('inscriptions', 'Participants Inscrits')
            ->onlyOnDetail();
    }
}
