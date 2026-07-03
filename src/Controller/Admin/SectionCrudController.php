<?php

namespace App\Controller\Admin;

use App\Controller\Admin\EtapeCrudController;
use App\Entity\Section;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
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
                ->setHelp('Définit l\'ordre d\'apparition du bloc sur la page (Ex: 1 pour le haut, 4 pour le bas).'),
            
            ChoiceField::new('disposition', 'Design de la section')
                ->setChoices([
                    'Texte centré (Classique)' => 'texte_centre',
                    'Image à Gauche + Texte à Droite' => 'img_gauche',
                    'Texte à Gauche + Image à Droite' => 'img_droite',
                    'Bannière pleine largeur' => 'banniere',
                    'Slider des Prestations' => 'slider_prestations',
                    'Bloc Info Pratique (Fleur & Note)' => 'info_pratique',
                    'Cheminement (Étapes)' => 'cheminement', // Ajouté pour correspondre à ton design
                ])
                ->setRequired(true)
                ->setEmptyData('texte_centre')
                ->setHelp('Sélectionnez la mise en page visuelle de ce bloc.')
                ->renderExpanded(false),
            
            TextField::new('titre', 'Titre du bloc')
                ->setRequired(false)
                ->setHelp('Le titre principal qui s\'affichera au-dessus de votre texte.'),

            ChoiceField::new('baliseHtml', 'Taille / Importance du titre')
                ->setChoices([
                    'Titre principal de section (H2)' => 'h2',
                    'Sous-titre (H3)' => 'h3',
                    'Petit titre (H4)' => 'h4',
                ])
                ->setRequired(false)
                ->setEmptyData('h2')
                ->setHelp('Définit la taille visuelle et l\'importance SEO du titre.'),
            
            TextareaField::new('contenu', 'Contenu textuel')
                ->setNumOfRows(8)
                ->setRequired(false)
                ->setHelp('Saisissez ici le corps de votre texte. Évitez d\'y inclure des titres, utilisez le champ "Titre du bloc" ci-dessus pour cela.'),
                            
            Field::new('imageFile', 'Illustration visuelle')
                ->setFormType(VichImageType::class)
                ->setRequired(false)
                ->hideOnIndex()
                ->setHelp('💡 Idéal pour les designs avec image latérale ou bannière.'),
                
            IntegerField::new('largeurMedia', 'Largeur de l\'image (px)')
                ->setRequired(false)
                ->hideOnIndex()
                ->setHelp('Optionnel. Laissez vide pour une adaptation automatique.'),
                
            IntegerField::new('hauteurMedia', 'Hauteur de l\'image (px)')
                ->setRequired(false)
                ->hideOnIndex()
                ->setHelp('Optionnel. Laissez vide pour une adaptation automatique.'),

            AssociationField::new('prestations', 'Prestations à lier au bloc')
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->setHelp('Si vous avez sélectionné la disposition "Slider des Prestations", cochez ici les offres qui doivent y figurer.')
                ->hideOnIndex(),   

            // Une seule ligne de collection propre et bien documentée pour Aya
            CollectionField::new('etapes', 'Construire les Étapes')
                ->useEntryCrudForm(EtapeCrudController::class)
                ->setHelp('Si vous avez choisi le design "Cheminement", ajoutez vos étapes ici. Les cercles et les traits dorés s\'adapteront tout seuls !')
                ->hideOnIndex(),
        ];
    }
}