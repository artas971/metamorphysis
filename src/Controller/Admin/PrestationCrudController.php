<?php

namespace App\Controller\Admin;

use App\Entity\Prestation;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Vich\UploaderBundle\Form\Type\VichImageType;

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

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addJsFile('js/admin_tarifs_dynamiques.js');
    }

    public function configureFields(string $pageName): iterable
    {
        // 1. Vue TABLEAU LISTING (/admin/prestation) : Compact et lisible sans défilement
        if ($pageName === Crud::PAGE_INDEX) {
            return [
                ImageField::new('imageName', '🖼️ Photo')
                    ->setBasePath('/uploads/prestations'),
                TextField::new('nom', '💆 Nom'),
                TextField::new('prixAffiche', '🏷️ Prix Affiché'),
                MoneyField::new('prix', '💶 Tarif Base')
                    ->setCurrency('EUR')
                    ->setStoredAsCents(false),
                IntegerField::new('minPersonnes', '👥 Min Pers'),
                IntegerField::new('maxPersonnes', '👥 Max Pers'),
                IntegerField::new('nombreSeances', '🎟️ Séances'),
                IntegerField::new('ordre', '🔢 Ordre'),
                BooleanField::new('estMisEnAvant', '⭐ En Vedette')
                    ->renderAsSwitch(true),
            ];
        }

        // 2. Vue FORMULAIRE DE CRÉATION / MODIFICATION
        return [
            TextField::new('nom', 'Nom de la prestation')
                ->setRequired(true)
                ->setHelp('Ex: Accompagnement Individuel, Séance d\'alignement...'),

            TextField::new('prixAffiche', '🏷️ Texte du prix affiché sur les cartes')
                ->setHelp('Texte commercial libre. Ex: "À partir de 80 €", "Entre 80 € et 120 €", "80 €", "Sur devis"... (si vide, affiche le tarif 1 personne)')
                ->setRequired(false),

            IntegerField::new('minPersonnes', '👥 Nombre minimum de personnes')
                ->setRequired(true)
                ->setHelp('Indiquez le minimum de participants (ex: 1 pour individuel, 2 pour couple).')
                ->setColumns(6),

            IntegerField::new('maxPersonnes', '👥 Nombre maximum de personnes')
                ->setRequired(true)
                ->setHelp('Indiquez le maximum de participants (ex: 1, 3, 5...). Les champs de tarifs ci-dessous s\'adaptent automatiquement.')
                ->setColumns(6),

            \EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField::new('tarifsParPersonneJson')
                ->setFormTypeOption('attr', ['id' => 'tarifs-json-input']),

            \EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField::new('prix')
                ->setFormTypeOption('attr', ['id' => 'prix-base-input']),

            IntegerField::new('nombreSeances', '🎟️ Nombre de séances incluses')
                ->setRequired(true)
                ->setHelp('Indiquez 1 pour un soin unique, ou plus s\'il s\'agit d\'un forfait/parcours (ex: 3, 5, 10).'),

            TextField::new('unitePrix', 'Unité de facturation')
                ->setHelp('Exemple : SÉANCE, FORFAIT, ACCOMPAGNEMENT. Par défaut : SÉANCE')
                ->setRequired(false),

            IntegerField::new('ordre', 'Position (Ordre)')
                ->setRequired(false)
                ->setHelp('Ex: 1 pour afficher en premier, 2 en deuxième, etc. Utilisé comme ordre par défaut.'),

            IntegerField::new('duree', 'Durée de la séance (en minutes)')
                ->setRequired(false)
                ->setHelp('Ex: 45. Laissez vide si la durée n\'est pas fixe ou non applicable.'),

            BooleanField::new('estMisEnAvant', 'Mettre en avant cette prestation')
                ->renderAsSwitch(true)
                ->setHelp('Activez cette option pour pousser ce soin en priorité sur le site.'),

            TextField::new('imageFile', 'Image illustrative du soin')
                ->setFormType(VichImageType::class)
                ->setHelp('💡 Conseil design : Pour un rendu optimal sur les cartes, privilégiez une photo épurée au format portrait ou carré (ratio 4:5 ou 1:1).'),
                
            ChoiceField::new('icone', 'Nombre de personnes (Icône)')
                ->setChoices([
                    '1 personne (Buste)' => 'bi-person',
                    '2 personnes (Couple)' => 'bi-people',
                    'Famille (Maison avec cœur)' => 'bi-house-heart', 
                    'Groupe (Réseau)' => 'bi-diagram-3'
                ])
                ->setRequired(false)
                ->renderExpanded()
                ->setHelp('Choisissez l\'icône représentant le nombre de personnes pour cette prestation.'),

            TextareaField::new('description', 'Description courte (Cartes)')
                ->setNumOfRows(4)
                ->setHelp('Présentez brièvement le déroulé du soin pour l\'aperçu général.'),
 
            TextareaField::new('descriptionComplementaire', 'Description complète (Page Détails)')  
                ->setNumOfRows(10)
                ->setHelp('Le texte détaillé du déroulé de la séance. Allez simplement à la ligne pour créer des paragraphes.'),
 
            UrlField::new('lienVideo', 'Lien Vidéo (YouTube, Vimeo, etc.)')
                ->setRequired(false)
                ->setHelp('Collez ici l\'URL complète de la vidéo de présentation.'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Prestation) {
            $this->syncPrestationPricing($entityInstance);
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Prestation) {
            $this->syncPrestationPricing($entityInstance);
        }
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function syncPrestationPricing(Prestation $prestation): void
    {
        $tarifs = $prestation->getTarifsParPersonne();
        $min = $prestation->getMinPersonnes();

        if (isset($tarifs[(string)$min]) && (float)$tarifs[(string)$min] > 0) {
            $prestation->setPrix((float) $tarifs[(string)$min]);
        } elseif (!empty($tarifs)) {
            $first = reset($tarifs);
            $prestation->setPrix((float) $first);
        }

        if (isset($tarifs['2'])) {
            $prestation->setPrixCouple((float) $tarifs['2']);
        }
        if (isset($tarifs['3'])) {
            $prestation->setPrixGroupe((float) $tarifs['3']);
        }
    }
}