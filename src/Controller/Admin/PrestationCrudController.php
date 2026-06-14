<?php

namespace App\Controller\Admin;

use App\Entity\Prestation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PrestationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Prestation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // On rend le nom obligatoire
            TextField::new('nom', 'Nom de la prestation')
                ->setRequired(true),

            // On rend le prix obligatoire
            NumberField::new('prix', 'Prix (€)')
                ->setNumDecimals(2)
                ->setRequired(true)
                ->setHelp('Exemple : 49.99'),

            // On rend la durée obligatoire
            IntegerField::new('duree', 'Durée (en minutes)')
                ->setRequired(true)
                ->setHelp('Exemple : 60 pour 1 heure'),

            TextEditorField::new('description', 'Description détaillée'),

            ImageField::new('imageName', 'Aperçu de l\'image')
                ->setBasePath('/uploads/prestations')
                ->hideOnForm(),

            TextField::new('imageFile', 'Télécharger une image')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),
        ];
    }
}