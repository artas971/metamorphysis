<?php

namespace App\Controller\Admin;

use App\Entity\Prestation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
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
            ->setHelp('index', 'Gerez ici votre catalogue de soins. Les modifications sont instantanément répercutées sur votre site public.');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom', 'Nom de la prestation')
                ->setRequired(true)
                ->setHelp('Ex: Accompagnement Individuel, Séance d\'alignement...'),

            MoneyField::new('prix', 'Tarif de la consultation')
                ->setCurrency('EUR')
                ->setStoredAsCents(false)
                ->setRequired(true)
                ->setHelp('Le prix sera affiché avec le symbole € de manière automatique.'),

            TextField::new('unitePrix', 'Unité de facturation')
                ->setHelp('Exemple : SÉANCE, FORFAIT, ACCOMPAGNEMENT. Par défaut : SÉANCE')
                ->setRequired(false),

            IntegerField::new('duree', 'Durée de la séance (en minutes)')
                ->setRequired(true)
                ->setHelp('Ex: 60 pour 1 heure.'),

            BooleanField::new('estMisEnAvant', 'Mettre en avant cette prestation')
                ->renderAsSwitch(true)
                ->setHelp('Activez cette option pour pousser ce soin en priorité sur le site.'),

            ImageField::new('imageName', 'Aperçu de la photo')
                ->setBasePath('/uploads/prestations')
                ->onlyOnIndex(),

            TextField::new('imageFile', 'Image illustrative du soin')
                ->setFormType(VichImageType::class)
                ->onlyOnForms()
                ->setHelp('💡 Conseil design : Pour un rendu optimal sur les cartes, privilégiez une photo épurée au format portrait ou carré (ratio 4:5 ou 1:1).'),
                
            ChoiceField::new('icone', 'Nombre de personnes (Icône)')
                ->setChoices([
                    '1 personne (Buste)' => 'bi-person',
                    '2 personnes (Couple)' => 'bi-people',
                    'Famille (Maison avec cœur)' => 'bi-house-heart', // Alternative chaleureuse
                    'Groupe (Réseau)' => 'bi-diagram-3'
                ])
                ->setRequired(false)
                ->renderExpanded()
                ->setHelp('Choisissez l\'icône représentant le nombre de personnes pour cette prestation.'),

            TextareaField::new('description', 'Description détaillée')
                ->setNumOfRows(8)
                ->setHelp('Présentez le déroulé du soin, ses bienfaits et les objectifs de la séance.'),
        ];
    }
}