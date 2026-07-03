<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField; 
class ReservationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    // 1. Configuration globale (Titre et Tri)
    public function configureCrud(Crud $crud): Crud
        {
            return $crud
                ->setEntityLabelInSingular('Réservation')
                ->setEntityLabelInPlural('Réservations')
                ->setPageTitle('index', 'Suivi des Réservations') 
                ->setHelp('index', 'Consultez et gérez les demandes de rendez-vous de vos clients. Vous pouvez valider les demandes en attente, suivre le statut des séances et garder un œil sur votre calendrier d\'activité en temps réel.');
        }

    // 2. Configuration des boutons (Actions)
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // On désactive le bouton "Créer" car les réservations viennent du site public
            ->disable(Action::NEW)
            // On ajoute une icône "Œil" pour voir le détail complet d'une réservation
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    // 3. Configuration des champs affichés
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            // Le client
            AssociationField::new('user', 'Client')
                ->setDisabled(true),

            // Téléphone (Uniquement en index)
            TextField::new('user.telephone', 'Téléphone Client')
                ->formatValue(function ($value, $entity) {
                    return $entity->getUser() ? $entity->getUser()->getTelephone() : 'Non renseigné';
                })
                ->onlyOnIndex(),

            // La prestation
            AssociationField::new('prestation', 'Soin réservé')
                ->setDisabled(true),

            // La date
            DateTimeField::new('dateRendezVous', 'Date et Heure du RDV')
                ->setFormat('dd/MM/yyyy HH:mm'),

            // Le statut avec badges
            ChoiceField::new('statut', 'Statut de la demande')
                ->setChoices([
                    'En attente' => 'En attente',
                    'Confirmé'   => 'Confirmé',
                    'Annulé'     => 'Annulé',
                ])
                ->renderAsBadges([
                    'En attente' => 'warning',
                    'Confirmé'   => 'success',
                    'Annulé'     => 'danger',
                ]),
        ];
    }
    
}