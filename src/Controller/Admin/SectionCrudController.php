<?php

namespace App\Controller\Admin;

use App\Controller\Admin\EtapeCrudController;
use App\Entity\Section;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
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
            // ======================================================
            // 1. CONFIGURATION GÉNÉRALE
            // ======================================================
            FormField::addFieldset('⚙️ Configuration Générale du Bloc')->setIcon('fas fa-cogs'),
            
            IntegerField::new('ordre', 'Position (Ordre)')
                ->setColumns('col-md-4')
                ->setHelp('Ex: 1 pour le haut, 4 pour le bas.'),
            
            ChoiceField::new('disposition', 'Design de la section')
                ->setColumns('col-md-4')
                ->setChoices([
                    'Texte centré (Classique)' => 'texte_centre',
                    'Image à Gauche + Texte à Droite' => 'img_gauche',
                    'Texte à Gauche + Image à Droite' => 'img_droite',
                    'Bannière pleine largeur' => 'banniere',
                    'Slider des Prestations' => 'slider_prestations',
                    'Bloc Info Pratique (Fleur & Note)' => 'info_pratique',
                    'Cheminement (Étapes)' => 'cheminement',
                    '👩‍💼 Profil Fondatrice (À Propos)' => 'presentation_expert',
                ])
                ->setRequired(true)->setEmptyData('texte_centre')->renderExpanded(false),
            
            ChoiceField::new('couleurFond', 'Couleur de fond')
                ->setColumns('col-md-4')
                ->setChoices([
                    'Pourpre (Classique)' => 'plum',
                    'Olive (Mise en avant)' => 'olive',
                ])->renderExpanded(false),

            // ======================================================
            // 2. CONTENU TEXTUEL (GAUCHE)
            // ======================================================
            FormField::addFieldset('📝 Textes Principaux')->setIcon('fas fa-align-left'),

            TextField::new('titre', 'Grand Titre')
                ->setColumns('col-md-8')
                ->setRequired(false),

            ChoiceField::new('baliseHtml', 'Taille du titre')
                ->setColumns('col-md-4')
                ->setChoices(['Titre (H2)' => 'h2', 'Sous-titre (H3)' => 'h3', 'Petit (H4)' => 'h4'])
                ->setRequired(false)->setEmptyData('h2')->hideOnIndex(),
            
            TextareaField::new('contenu', 'Contenu textuel')
                ->setColumns(12)
                ->setNumOfRows(8)->setRequired(false)
                ->setHelp('Utilisez l\'éditeur pour vos paragraphes ou le bouton "Source" pour le HTML.'),

            // ======================================================
            // 3. IMAGE & POSITIONNEMENT (DROITE)
            // ======================================================
            FormField::addFieldset('🖼️ Image Principale & Réglages')->setIcon('fas fa-image'),
            
            Field::new('imageFile', 'Télécharger l\'illustration')
                ->setColumns(12)
                ->setFormType(VichImageType::class)->setRequired(false)->hideOnIndex(),
                
            IntegerField::new('imagePosX', 'Décalage Horizontal (%)')
                ->setColumns('col-md-6')
                ->setHelp('Positif (ex: 10) vers la droite, négatif (ex: -10) vers la gauche.')->hideOnIndex(),
                
            IntegerField::new('imagePosY', 'Décalage Vertical (px)')
                ->setColumns('col-md-6')
                ->setHelp('Positif (ex: 50) vers le bas, négatif (ex: -50) vers le haut.')->hideOnIndex(),

            IntegerField::new('largeurMedia', 'Largeur forcée (px)')->setColumns('col-md-6')->setRequired(false)->hideOnIndex(),
            IntegerField::new('hauteurMedia', 'Hauteur forcée (px)')->setColumns('col-md-6')->setRequired(false)->hideOnIndex(),

            // ======================================================
            // 4. BOÎTE DE CITATION SUPERPOSÉE
            // ======================================================
            FormField::addFieldset('✨ Encart "Citation" Superposé')->setIcon('fas fa-quote-right'),

            TextareaField::new('citation', 'Texte de la citation')
                ->setColumns(12)
                ->setHelp('La phrase forte. Si le texte est très long, utilisez la hauteur max ci-dessous.')->setRequired(false),

            IntegerField::new('citationHauteurMax', 'Hauteur Max avant scroll (px)')
                ->setColumns(12)
                ->setHelp('Ex: 300. Laissez vide pour une hauteur infinie.')->hideOnIndex(),

            IntegerField::new('citationPosX', 'Décalage Horizontal (%)')
                ->setColumns('col-md-4')
                ->setHelp('Ex: -10 (déborde à gauche), 5 (décalé à droite).')->hideOnIndex(),
                
            IntegerField::new('citationPosY', 'Décalage Vertical (px)')
                ->setColumns('col-md-4')
                ->setHelp('Ex: -150 (monte sur la photo).')->hideOnIndex(),

            IntegerField::new('citationLargeur', 'Largeur de la boîte (%)')
                ->setColumns('col-md-4')
                ->setHelp('Ex: 90 (recommandé).')->hideOnIndex(),

            ChoiceField::new('citationCouleurFond', 'Couleur du Fond')
                ->setColumns('col-md-6')
                ->setChoices([
                'Noir profond' => 'meta-black', 'Pourpre (Plum)' => 'meta-plum', 'Vert Olive' => 'meta-olive',
                'Vert Sauge' => 'meta-sage', 'Or (Gold)' => 'meta-gold', 'Ivoire' => 'meta-ivory',
            ])->hideOnIndex(),

            ChoiceField::new('citationCouleurTexte', 'Couleur du Texte')
                ->setColumns('col-md-6')
                ->setChoices([
                'Noir profond' => 'meta-black', 'Pourpre (Plum)' => 'meta-plum', 'Vert Olive' => 'meta-olive',
                'Vert Sauge' => 'meta-sage', 'Or (Gold)' => 'meta-gold', 'Ivoire' => 'meta-ivory',
            ])->hideOnIndex(),

            // ======================================================
            // 5. AUTRES (PRESTATIONS / ÉTAPES)
            // ======================================================
            FormField::addFieldset('🔧 Modules Avancés (Slider / Cheminement)')->setIcon('fas fa-layer-group'),

            AssociationField::new('prestations', 'Lier des prestations')->setColumns(12)->setFormTypeOptions(['by_reference' => false])->hideOnIndex(),   
            CollectionField::new('etapes', 'Construire les Étapes')->setColumns(12)->useEntryCrudForm(EtapeCrudController::class)->hideOnIndex(),
        ];
    }
}