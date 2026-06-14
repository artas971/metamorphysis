<?php

namespace App\Controller\Admin;

use App\Entity\Section;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
 use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Vich\UploaderBundle\Form\Type\VichImageType; 
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
// Supprime l'ancienne ligne : use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
// Et ajoute celle-ci :

class SectionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Section::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('ordre', 'Position (ex: 1 (haut), 2, 3...)')
                ->setHelp('Définit l\'ordre d\'affichage sur la page.'),
            
            // Le menu déroulant pour choisir le design de ce bloc précis
            ChoiceField::new('disposition', 'Design de la section')
                ->setChoices([
                    'Texte centré (Classique)' => 'texte_centre',
                    'Image à Gauche + Texte à Droite' => 'img_gauche',
                    'Texte à Gauche + Image à Droite' => 'img_droite',
                    'Bannière pleine largeur' => 'banniere',
                ])
                ->setRequired(true) // Force la cliente à choisir
                ->renderExpanded(false), // S'assure que c'est bien une liste déroulante
            
// On remplace TextEditorField par TextareaField et on lui donne 8 lignes de hauteur
            TextareaField::new('contenu', 'Texte (HTML autorisé)')
                            ->setNumOfRows(8)
                            ->setRequired(false) // Ajoute cette ligne ici
                            ->setHelp('Tu peux utiliser des balises HTML basiques comme <b> pour le gras ou <br> pour sauter une ligne.'),
                            
            // Configuration de l'upload d'image/vidéo
            Field::new('imageFile', 'Image')
                ->setFormType(VichImageType::class)
                ->setRequired(false) // Très important pour que l'image soit facultative
                ->hideOnIndex(),
                
            IntegerField::new('largeurMedia', 'Largeur image (px)')->setRequired(false)->hideOnIndex(),
            IntegerField::new('hauteurMedia', 'Hauteur image (px)')->setRequired(false)->hideOnIndex(),
        ];
    }
}