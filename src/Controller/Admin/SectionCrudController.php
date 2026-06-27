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
            // 1. LE DESIGN
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
                ->renderExpanded(false),

            // 2. L'ORDRE
            IntegerField::new('ordre', 'Position (ex: 1, 2, 3...)')
                ->setHelp('Définit l\'ordre d\'affichage de ce bloc de haut en bas sur la page.'),

            // 3. LE TEXTE
            TextField::new('titre', 'Titre de la section (Optionnel)'),
            TextareaField::new('contenu', 'Texte (HTML autorisé)')
                ->setNumOfRows(8)
                ->setRequired(false)
                ->setHelp('Tu peux utiliser des balises HTML basiques comme <b> pour le gras ou <br> pour sauter une ligne.'),

            // 4. L'IMAGE (Via VichUploader)
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