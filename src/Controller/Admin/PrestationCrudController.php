<?php

namespace App\Controller\Admin;

use App\Entity\Prestation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
 use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
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
                // Tes autres champs (nom, prix, description...)
                
                // Le champ pour UPLOADER l'image (visible uniquement quand on ajoute/modifie)
                TextField::new('imageFile', 'Image de couverture')
                    ->setFormType(VichImageType::class)
                    ->onlyOnForms(),
                    
                // Le champ pour AFFICHER l'image (visible dans la liste du tableau de bord)
                ImageField::new('imageName', 'Aperçu')
                    ->setBasePath('/uploads/prestations')
                    ->hideOnForm(),
            ];
        }
    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
