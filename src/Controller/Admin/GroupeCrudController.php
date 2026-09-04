<?php

namespace App\Controller\Admin;

use App\Entity\Groupe;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GroupeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Groupe::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular("Groupe d'Accompagnement")
            ->setEntityLabelInPlural("Accompagnements en Groupe (Cohortes)")
            ->setDefaultSort(['dateCreation' => 'DESC'])
            ->setHelp('index', 'Gérez ici vos cohortes d\'accompagnement en groupe. Vous pouvez planifier les séances successives en reconduisant automatiquement les participants convenus lors de vos séances.');
    }

    public function configureActions(Actions $actions): Actions
    {
        $planifierSeance = Action::new('planifierSeance', 'Planifier la prochaine séance', 'fa fa-calendar-plus text-warning')
            ->linkToRoute('admin_groupe_planifier_seance', function (Groupe $groupe) {
                return ['id' => $groupe->getId()];
            });

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $planifierSeance)
            ->add(Crud::PAGE_DETAIL, $planifierSeance);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', '#')->hideOnForm();

        yield TextField::new('nom', '🏷️ Nom du Groupe')
            ->setHelp('Ex: Cercle d\'Éveil du Dimanche, Cohorte Sérénité...')
            ->setRequired(true);

        yield AssociationField::new('prestation', '💆 Prestation rattachée')
            ->setRequired(true)
            ->setHelp('Sélectionnez la prestation collective d\'accompagnement en groupe.');

        yield ChoiceField::new('statut', 'Statut du Groupe')
            ->setChoices([
                'Actif (Séances en cours)' => 'Actif',
                'Clôturé (Cycle achevé)' => 'Clôturé',
            ])
            ->renderAsBadges([
                'Actif' => 'success',
                'Clôturé' => 'secondary',
            ]);

        yield TextField::new('sessionsProgression', '📅 Séances organisées')
            ->onlyOnIndex()
            ->formatValue(function ($value, Groupe $entity) {
                $count = $entity->getNombreSessions();
                $last = $entity->getDerniereSession();
                $lastDate = $last && $last->getDateDebut() ? $last->getDateDebut()->format('d/m/Y') : 'Aucune';
                return sprintf('<strong>%d séance(s)</strong> (Dernière : %s)', $count, $lastDate);
            });

        yield AssociationField::new('membres', '👥 Participants réguliers (Cohorte)')
            ->setHelp('Sélectionnez les membres qui composent ce groupe d\'accompagnement.')
            ->formatValue(function ($value, Groupe $entity) {
                return sprintf('%d participant(s)', $entity->getMembres()->count());
            });

        yield DateTimeField::new('dateCreation', 'Créé le')
            ->onlyOnDetail()
            ->setFormat('dd/MM/yyyy HH:mm');

        yield TextareaField::new('description', 'Notes & Thématique du groupe')
            ->hideOnIndex();

        yield AssociationField::new('sessions', 'Historique des Séances')
            ->onlyOnDetail();
    }
}
