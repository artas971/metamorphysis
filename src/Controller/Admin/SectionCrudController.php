<?php

namespace App\Controller\Admin;

use App\Entity\Section;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;
 
class SectionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Section::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('ordre', 'Position')
                ->setHelp('Définit l\'ordre d\'affichage sur la page.'),
            
            ChoiceField::new('disposition', 'Design de la section')
                ->setChoices([
                    'Texte centré (Classique)' => 'texte_centre',
                    'Image à Gauche + Texte à Droite' => 'img_gauche',
                    'Texte à Gauche + Image à Droite' => 'img_droite',
                    'Bannière pleine largeur' => 'banniere',
                    'Slider des Prestations' => 'slider_prestations',
                    'Bloc Info Pratique (Fleur & Note)' => 'info_pratique', // <-- NOUVELLE LIGNE // <-- LA NOUVELLE LIGNE EST ICI
                ])
                ->setRequired(true)
                ->setEmptyData('texte_centre') // Force cette valeur si vide
                ->renderExpanded(false),
            
            TextareaField::new('contenu', 'Texte (HTML autorisé)')
                ->setNumOfRows(8)
                ->setRequired(false),
                            
            Field::new('imageFile', 'Image')
                ->setFormType(VichImageType::class)
                ->setRequired(false)
                ->hideOnIndex(),
                
            IntegerField::new('largeurMedia', 'Largeur image (px)')->setRequired(false)->hideOnIndex(),
            IntegerField::new('hauteurMedia', 'Hauteur image (px)')->setRequired(false)->hideOnIndex(),
            
            // 5. LE SEO
            ChoiceField::new('baliseHtml', 'Type de conteneur SEO')
                ->setChoices([
                    'Section standard' => 'section',
                    'Article indépendant' => 'article',
                    'Bloc complémentaire' => 'aside',
                ]),
            AssociationField::new('prestations', 'Prestations à afficher')
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->setHelp('Si vous avez choisi le design "Slider", sélectionnez ici les prestations qui s\'afficheront dedans.')
                ->hideOnIndex(),    
        ];
    }
}