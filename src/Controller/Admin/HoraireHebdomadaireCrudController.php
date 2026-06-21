<?php

namespace App\Controller\Admin;

use App\Entity\HoraireHebdomadaire;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class HoraireHebdomadaireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return HoraireHebdomadaire::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // On transforme l'entier brut en un menu déroulant clair
            ChoiceField::new('jour', 'Jour de la semaine')
                ->setChoices([
                    'Lundi' => 1,
                    'Mardi' => 2,
                    'Mercredi' => 3,
                    'Jeudi' => 4,
                    'Vendredi' => 5,
                    'Samedi' => 6,
                    'Dimanche' => 7,
                ]),
            
            // L'interrupteur actif/inactif
            BooleanField::new('estOuvert', 'Cabinet Ouvert'),
            
            // On formate les heures pour retirer les secondes (HH:mm)
            TimeField::new('ouvertureMatin', 'Début Matin')
                ->setFormat('HH:mm')
                ->setRequired(false), // N'est pas obligatoire si le cabinet est fermé
                
            TimeField::new('fermetureMatin', 'Fin Matin')
                ->setFormat('HH:mm')
                ->setRequired(false),
                
            TimeField::new('ouvertureApresMidi', 'Début Après-Midi')
                ->setFormat('HH:mm')
                ->setRequired(false),
                
            TimeField::new('fermetureApresMidi', 'Fin Après-Midi')
                ->setFormat('HH:mm')
                ->setRequired(false),
        ];
    }
}