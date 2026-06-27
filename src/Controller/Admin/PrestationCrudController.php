<?php

namespace App\Controller\Admin;

use App\Entity\Prestation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
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
                            ->onlyOnForms()
                            // Ajout du conseil pour ta cliente ici :
                            ->setHelp('💡 Conseil design : Pour un rendu optimal sur la page, privilégiez une image au format portrait ou carré (ratio 4:5 ou 1:1).'),
                
                ChoiceField::new('icone', 'Icône d\'illustration')
                ->setChoices([
                    'Individuel (Buste)' => 'bi-person',
                    'Couple (Deux personnes)' => 'bi-people',
                    'Famille / Groupe' => 'bi-diagram-3',
                    'Éclosion (Fleur)' => 'bi-flower1',
                    'Alignement (Soleil)' => 'bi-brightness-high',
                    'Observation (Œil)' => 'bi-eye',
                    'Analyse (Loupe)' => 'bi-search',
                    'Étoile (Premium)' => 'bi-star',
                    'Coeur (Écoute)' => 'bi-heart'
                ])
                ->renderExpanded()
                ->setHelp('Choisissez l\'icône qui s\'affichera sur la carte de la page d\'accueil.'),
        ];
        
    }
}