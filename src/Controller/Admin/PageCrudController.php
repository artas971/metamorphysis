<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        /** @var Page $page */
        $page = $context->getEntity()->getInstance();
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        $postData = $context->getRequest()->request->all();
        $submitButtonName = $postData['ea']['editForm']['btn'] 
            ?? $postData['ea']['newForm']['btn'] 
            ?? $context->getRequest()->request->get('btn') 
            ?? $context->getRequest()->query->get('btn') 
            ?? $action;

        // Si c'est la création d'une nouvelle page (NEW) OU si l'administrateur a cliqué sur "Enregistrer & Continuer d'Éditer" (saveAndContinue)
        if ($action === Action::NEW || $submitButtonName === Action::SAVE_AND_CONTINUE || $submitButtonName === 'saveAndContinue') {
            return $this->redirect(
                $adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::EDIT)
                    ->setEntityId($page->getId())
                    ->generateUrl()
            );
        }

        // Sinon ("Enregistrer & Quitter" / saveAndReturn), redirection vers le tableau de bord des pages (/admin/page)
        return $this->redirect(
            $adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Page')
            ->setEntityLabelInPlural('Pages du Site')
            ->setPageTitle('index', 'Gestion du Contenu des Pages')
            ->setHelp('index', '📑 <b>Bienvenue dans le gestionnaire de pages !</b> Vous pouvez ajouter de nouvelles pages ou éditer les textes et la disposition des pages existantes. Cliquez sur "Ajouter un élément" dans le Constructeur de blocs pour composer le design de votre page.')
            ->overrideTemplate('crud/edit', 'admin/page/edit.html.twig')
            ->overrideTemplate('crud/new', 'admin/page/new.html.twig');
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile('css/admin_builder.css')
            ->addJsFile('js/admin_builder.js');
    }

    public function configureActions(Actions $actions): Actions
    {
        $previewAction = Action::new('preview', '👁️ Voir sur le site', 'fa fa-external-link-alt')
            ->linkToRoute('app_page_preview', function (Page $page) {
                return ['slug' => $page->getSlug()];
            })
            ->setHtmlAttributes(['target' => '_blank'])
            ->addCssClass('btn btn-outline-info');

        return $actions
            // Boutons lors de la création d'une nouvelle page (NEW)
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('✨ Créer la page & Ouvrir l\'Éditeur')->setIcon('fa fa-magic')->addCssClass('btn-success');
            })
            // Boutons lors de l'édition d'une page existante (EDIT)
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('💾 Enregistrer & Quitter (Retour aux Pages)')->setIcon('fa fa-check')->addCssClass('btn-primary');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
                return $action->setLabel('🔄 Enregistrer & Continuer d\'Éditer')->setIcon('fa fa-sync')->addCssClass('btn-outline-primary');
            })
            ->add(Crud::PAGE_INDEX, $previewAction)
            ->add(Crud::PAGE_EDIT, $previewAction);
    }

    public function configureFields(string $pageName): iterable
    {
        // 1. Vue TABLEAU LISTING (/admin/page) : Affichage compact et épuré
        if ($pageName === Crud::PAGE_INDEX) {
            return [
                TextField::new('titre', '📄 Titre de la page'),
                SlugField::new('slug', '🔗 Adresse URL (Slug)')->setTargetFieldName('titre'),
                BooleanField::new('isPublished', '🌐 En Ligne')->renderAsSwitch(true),
                BooleanField::new('afficherMenu', '🧭 Menu Haut')->renderAsSwitch(true),
                IntegerField::new('ordreMenu', '🔢 Ordre'),
                BooleanField::new('afficherFooter', '🦶 Footer')->renderAsSwitch(true),
            ];
        }

        // 2. Vue FORMULAIRE DE MODIFICATION / CRÉATION (Page Builder)
        return [
            FormField::addFieldset('📌 Informations de la Page')->setIcon('fas fa-info-circle'),

            TextField::new('titre', 'Titre de la page')
                ->setColumns(12)
                ->setHelp('Ex: À propos, Nos Prestations, Informations...'),
            
            SlugField::new('slug', 'URL de la page (Slug)')
                ->setTargetFieldName('titre')
                ->setColumns(12)
                ->setHelp('L\'adresse web (ex: <code>metamorphysis.com/a-propos</code>). Se génère tout seul.'),

            FormField::addFieldset('👁️ Affichage & Publication')->setIcon('fas fa-eye'),

            BooleanField::new('isPublished', 'Publier la page (Mettre en ligne)')
                ->setColumns(12)
                ->setHelp('<b>Coché = En ligne pour le public</b>. <b>Décoché = Mode Brouillon</b>.'),

            BooleanField::new('afficherMenu', 'Afficher dans le menu du haut (Navbar)')
                ->setColumns(12)
                ->setHelp('<b>Coché = Ajouter le lien dans la barre de navigation du haut (Navbar)</b>.'),

            BooleanField::new('afficherFooter', 'Afficher dans le pied de page (Footer)')
                ->setColumns(12)
                ->setHelp('<b>Coché = Ajouter le lien en bas de page dans le Footer (ex: Mentions Légales)</b>.'),

            IntegerField::new('ordreMenu', '🔢 Ordre d\'affichage dans le menu')
                ->setColumns(12)
                ->setHelp('💡 <code>1</code> = premier lien à gauche, <code>2</code> = deuxième lien, etc.')
                ->setEmptyData(0),

            BooleanField::new('afficherTitre', 'Afficher le titre en haut de page')
                ->setColumns(12)
                ->setHelp('Décochez pour la page d\'Accueil.'),

            ChoiceField::new('fondBlocsUnifie', '🎨 Arrière-plan global de la page')
                ->setColumns(12)
                ->setChoices([
                    '🍷 Dégradé Pourpre (Recommandé)' => 'pourpre',
                    '🎨 Couleurs par bloc'           => 'individuel',
                    '🟩 Vert Olive'                  => 'olive',
                ])
                ->setRequired(false)
                ->setEmptyData('pourpre')
                ->setHelp('💡 "Dégradé Pourpre" permet à tout le fond du site de s\'afficher de façon fluide et ininterrompue de haut en bas.'),

            TextareaField::new('metaDescription', 'Description pour Google (SEO)')
                ->setColumns(12)
                ->setHelp('💡 <b>Astuce référencement :</b> Rédigez 1 à 2 phrases courtes décrivant le contenu de cette page pour Google.')
                ->setRequired(false),

            FormField::addFieldset('🧱 Constructeur de Blocs de Contenu (Page Builder)')->setIcon('fas fa-layer-group'),

            CollectionField::new('sections', 'Blocs de la page')
                ->setColumns(12)
                ->useEntryCrudForm(SectionCrudController::class)
                ->setHelp('💡 <b>Comment composer votre page ?</b> Cliquez sur "Ajouter un élément" ci-dessous. Chaque bloc peut être un texte centré, une photo avec texte, un carrousel de prestations ou un parcours d\'étapes. Renseignez la "Position" (1, 2, 3...) dans chaque bloc pour choisir quel bloc passe en haut ou en bas.')
        ];
    }
}