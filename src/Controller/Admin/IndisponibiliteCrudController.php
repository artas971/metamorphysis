<?php

namespace App\Controller\Admin;

use App\Entity\Indisponibilite;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class IndisponibiliteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Indisponibilite::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // Un champ texte clair avec un exemple pour t'aiguiller
            TextField::new('titre', 'Motif (ex: Vacances, Formation...)'),
            
            // Formatage de la date à la française (Jour/Mois/Année Heure:Minute)
            // L'option renderAsNativeWidget() permet d'afficher un beau calendrier cliquable
            DateTimeField::new('debut', 'Date et heure de début')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->renderAsNativeWidget(),
                
            DateTimeField::new('fin', 'Date et heure de fin')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->renderAsNativeWidget(),
        ];
    }
}