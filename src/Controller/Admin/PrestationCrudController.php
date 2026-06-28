<?php

namespace App\Controller\Admin;

use App\Entity\Prestation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class PrestationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Prestation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Prestation')
            ->setEntityLabelInPlural('Prestations')
            ->setHelp('index', 'Gérez ici votre catalogue de soins. Les modifications sont instantanément répercutées sur votre site public.');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom', 'Nom de la prestation')
                ->setRequired(true),

            MoneyField::new('prix', 'Tarif')
                ->setCurrency('EUR')
                ->setStoredAsCents(false)
                ->setRequired(true)
                ->setHelp('Le prix sera affiché avec le symbole € automatiquement.'),

            IntegerField::new('duree', 'Durée (minutes)')
                ->setRequired(true),

            // Ajout du bouton magique pour la mise en avant
            BooleanField::new('estMisEnAvant', 'Mise en avant')
                ->renderAsSwitch(true)
                ->setHelp('Activez cette option pour afficher ce soin en tête de liste sur la page d\'accueil.'),

            TextEditorField::new('description', 'Description détaillée')
                ->setNumOfRows(10),

            ImageField::new('imageName', 'Aperçu')
                ->setBasePath('/uploads/prestations')
                ->onlyOnIndex(),

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
            TextField::new('imageFile', 'Image du soin')
                ->setFormType(VichImageType::class)
                ->onlyOnForms()
                ->setHelp('Téléchargez une photo de haute qualité pour illustrer ce soin.'),
        ];
        
    }
}