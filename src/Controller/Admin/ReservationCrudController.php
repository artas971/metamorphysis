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
            ->setDefaultSort(['dateRendezVous' => 'DESC']); // Trie par date décroissante
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

            // On affiche le client, mais on bloque la modification
            AssociationField::new('user', 'Client')
                ->setDisabled(true),

            // On affiche la prestation, mais on bloque la modification
            AssociationField::new('prestation', 'Soin réservé')
                ->setDisabled(true),

            // La date reste modifiable si l'admin veut décaler le RDV en accord avec le client
            DateTimeField::new('dateRendezVous', 'Date et Heure du RDV')
                ->setFormat('dd/MM/yyyy HH:mm'),

            // Le statut avec le système de badges colorés
            ChoiceField::new('statut', 'Statut de la demande')
                ->setChoices([
                    'En attente' => 'En attente',
                    'Confirmé' => 'Confirmé',
                    'Annulé' => 'Annulé',
                ])
                ->renderAsBadges([
                    'En attente' => 'warning', // Jaune
                    'Confirmé' => 'success', // Vert
                    'Annulé' => 'danger',    // Rouge
                ]),
        ];
    }
}