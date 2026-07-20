<?php

namespace App\Controller\Admin;

use App\Entity\Seance; // On utilise la nouvelle entité Seance
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField; 

class SeanceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Seance::class;
    }

    // 1. Configuration globale (Titre et Tri)
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Séance')
            ->setEntityLabelInPlural('Séances')
            ->setPageTitle('index', 'Suivi des Séances') 
            ->setDefaultSort(['dateRendezVous' => 'DESC']) // Tri par date la plus récente
            ->setHelp('index', 'Consultez et gérez les séances des parcours d\'accompagnement de vos clients. Vous pouvez valider les demandes de planification, suivre le statut des séances et adapter le calendrier d\'activité en temps réel.');
    }

    // 2. Configuration des boutons (Actions)
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // On désactive le bouton "Créer" car les séances sont générées automatiquement lors de l'achat d'un soin
            ->disable(Action::NEW)
            // On ajoute une icône "Œil" pour voir le détail complet d'une séance si besoin
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    // 3. Configuration des champs affichés
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            // Le client rattaché
            AssociationField::new('user', 'Client')
                ->setDisabled(true),

            // Téléphone (Uniquement visible dans la liste globale)
            TextField::new('user.telephone', 'Téléphone Client')
                ->formatValue(function ($value, $entity) {
                    return $entity->getUser() ? $entity->getUser()->getTelephone() : 'Non renseigné';
                })
                ->onlyOnIndex(),

            // Le parcours global auquel appartient la séance
            AssociationField::new('prestation', 'Soin / Parcours')
                ->setDisabled(true),

            // Le numéro de l'étape dans le forfait
            IntegerField::new('numero', 'Séance N°')
                ->setDisabled(true)
                ->setHelp('Position chronologique de cette séance dans le parcours de l\'accompagnement.'),

            // La date fixée par le client
            DateTimeField::new('dateRendezVous', 'Date et Heure du RDV')
                ->setFormat('dd/MM/yyyy HH:mm'),

            // Le statut avec les badges de couleur adaptés
            ChoiceField::new('statut', 'Statut de la demande')
                ->setChoices([
                    'Non planifiée'           => 'Non planifiée',
                    'En attente de validation' => 'En attente de validation',
                    'Confirmé'                => 'Confirmé',
                    'Annulé'                  => 'Annulé',
                ])
                ->renderAsBadges([
                    'Non planifiée'           => 'secondary',
                    'En attente de validation' => 'warning',
                    'Confirmé'                => 'success',
                    'Annulé'                  => 'danger',
                ]),
        ];
    }
}