<?php

namespace App\Controller\Admin;

use App\Entity\Prestation;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PrestationCrudController extends AbstractCrudController
{
    #[Route('/admin/prestation/{entityId}/edit', name: 'admin_prestation_edit_legacy', priority: 10)]
    public function legacyEdit(string $entityId, EntityManagerInterface $em): RedirectResponse
    {
        $prestation = $em->getRepository(Prestation::class)->find($entityId);
        $target = ($prestation && $prestation->getSlug()) ? $prestation->getSlug() : $entityId;

        return $this->redirectToRoute('admin_prestation_edit', ['entityId' => $target]);
    }

    #[Route('/admin/prestation/new', name: 'admin_prestation_new_legacy', priority: 10)]
    public function legacyNew(): RedirectResponse
    {
        return $this->redirectToRoute('admin_prestation_new');
    }

    public static function getEntityFqcn(): string
    {
        return Prestation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Prestation')
            ->setEntityLabelInPlural('Prestations')
            ->setDefaultSort(['ordre' => 'ASC', 'id' => 'ASC'])
            ->setHelp('index', 'Gérez ici votre catalogue de soins. Les modifications sont instantanément répercutées sur votre site public.');
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addJsFile('js/admin_tarifs_dynamiques.js?v=2.2');
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
            return $action->setLabel('Modifier')
                ->linkToUrl(function (Prestation $prestation) {
                    return $this->container->get(AdminUrlGenerator::class)
                        ->setController(self::class)
                        ->setAction(Action::EDIT)
                        ->setEntityId($prestation->getSlug() ?: $prestation->getId())
                        ->generateUrl();
                });
        });

        $actions->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action->setLabel('Ajouter une prestation');
        });

        return $actions;
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        /** @var Prestation $prestation */
        $prestation = $context->getEntity()->getInstance();
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        $postData = $context->getRequest()->request->all();
        $submitButtonName = $postData['ea']['editForm']['btn'] 
            ?? $postData['ea']['newForm']['btn'] 
            ?? $context->getRequest()->request->get('btn') 
            ?? $context->getRequest()->query->get('btn') 
            ?? $action;

        if ($action === Action::NEW || $submitButtonName === Action::SAVE_AND_CONTINUE || $submitButtonName === 'saveAndContinue') {
            return $this->redirect(
                $adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::EDIT)
                    ->setEntityId($prestation->getSlug() ?: $prestation->getId())
                    ->generateUrl()
            );
        }

        return $this->redirect(
            $adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
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
                IntegerField::new('nombrePrix', '🏷️ Nb Prix'),
                IntegerField::new('nombreSeances', '🎟️ Séances'),
                IntegerField::new('ordre', '🔢 Ordre'),
                BooleanField::new('estCollectif', '👥 Groupe')
                    ->renderAsSwitch(true),
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

            ChoiceField::new('nombrePrix', '🏷️ Nombre de prix proposés')
                ->setChoices([
                    '1 prix (Tarif unique)' => 1,
                    '2 prix (2 formules / tarifs)' => 2,
                    '3 prix (3 formules / tarifs)' => 3,
                    '4 prix (4 formules / tarifs)' => 4,
                    '5 prix (5 formules / tarifs)' => 5,
                    '6 prix (6 formules / tarifs)' => 6,
                ])
                ->setRequired(true)
                ->setHelp('Indiquez combien de prix ou formules sont proposés pour cette prestation. Le bloc ci-dessous s\'adapte instantanément.')
                ->setColumns(6),

            HiddenField::new('minPersonnes')
                ->setFormTypeOption('attr', ['id' => 'min-personnes-input']),

            HiddenField::new('maxPersonnes')
                ->setFormTypeOption('attr', ['id' => 'max-personnes-input']),

            HiddenField::new('tarifsParPersonneJson')
                ->setFormTypeOption('attr', ['id' => 'tarifs-json-input']),

            HiddenField::new('prix')
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

            FormField::addFieldset('👥 Accompagnement en Groupe & Atelier Collectif')
                ->setHelp('Activez cette option si cette prestation est un accompagnement collectif / atelier en groupe avec seuil de participants et pré-réservation.'),

            BooleanField::new('estCollectif', 'Activer le mode Atelier Collectif / Groupe')
                ->renderAsSwitch(true)
                ->setHelp('Si activé, la fiche publique affichera une jauge de participants et le mode pré-réservation avec empreinte bancaire à 0 € sans débit immédiat.')
                ->setColumns(6),

            TextField::new('labelCollectif', '🏷️ Intitulé du bouton / Type de collectif')
                ->setHelp('Texte affiché sur le bouton de la carte (ex : "ATELIER COLLECTIF", "ACCOMPAGNEMENT EN GROUPE", "CERCLE D\'ÉVEIL"...). Par défaut : "ATELIER COLLECTIF".')
                ->setRequired(false)
                ->setColumns(6),

            IntegerField::new('seuilMinimum', '👥 Nombre Minimum de Participants requis')
                ->setRequired(false)
                ->setHelp('Nombre minimum d\'inscrits requis pour valider la séance et prélever les participations (défaut : 5).')
                ->setColumns(6),

            IntegerField::new('capaciteMaximale', '👥 Capacité Maximale de la Salle')
                ->setRequired(false)
                ->setHelp('Plafond maximal de participants pour cette session de groupe (ex : 8, 10, 12).')
                ->setColumns(6),

            IntegerField::new('delaiLimiteHeures', '⏳ Délai Limite de Confirmation (en heures)')
                ->setRequired(false)
                ->setHelp('Nombre d\'heures avant l\'atelier pour statuer si le seuil est atteint (ex: 24h ou 48h).')
                ->setColumns(6),

            TextField::new('recurrence', '🔁 Fréquence / Récurrence')
                ->setRequired(false)
                ->setHelp('Ex: "Chaque samedi de 14h00 à 15h30", "Un dimanche sur deux"...')
                ->setColumns(6),

            TextareaField::new('messageDateADefinir', '💬 Message d\'information (quand la date est à définir)')
                ->setRequired(false)
                ->setNumOfRows(3)
                ->setHelp('Texte affiché sur la fiche publique lorsque la prochaine date n\'est pas encore fixée.')
                ->setColumns(12),
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

        if (isset($tarifs[(string)$min])) {
            $val = $tarifs[(string)$min];
            $prix = is_array($val) ? ($val['prix'] ?? 0) : $val;
            $prestation->setPrix((float) $prix);
        } elseif (!empty($tarifs)) {
            $first = reset($tarifs);
            $prix = is_array($first) ? ($first['prix'] ?? 0) : $first;
            $prestation->setPrix((float) $prix);
        }

        if (isset($tarifs['2'])) {
            $val2 = $tarifs['2'];
            $prestation->setPrixCouple((float) (is_array($val2) ? ($val2['prix'] ?? 0) : $val2));
        }
        if (isset($tarifs['3'])) {
            $val3 = $tarifs['3'];
            $prestation->setPrixGroupe((float) (is_array($val3) ? ($val3['prix'] ?? 0) : $val3));
        }

        if (!empty($prestation->getNom())) {
            $prestation->setSlug((new \Symfony\Component\String\Slugger\AsciiSlugger())->slug($prestation->getNom())->lower()->toString());
        }
    }
}