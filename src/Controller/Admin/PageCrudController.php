<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;

class PageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Page::class;
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
        $previewAction = Action::new('preview', 'Voir l\'aperçu direct', 'fa fa-eye')
            ->linkToRoute('app_page_preview', function (Page $page) {
                return ['slug' => $page->getSlug()];
            })
            ->setHtmlAttributes(['target' => '_blank'])
            ->addCssClass('btn btn-outline-info');

        return $actions
            ->add(Crud::PAGE_INDEX, $previewAction)
            ->add(Crud::PAGE_EDIT, $previewAction);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addFieldset('📌 Informations de la Page')->setIcon('fas fa-info-circle'),

            TextField::new('titre', 'Titre de la page')
                ->setColumns('col-12 col-md-6')
                ->setHelp('Ex: À propos, Nos Prestations, Informations...'),
            
            SlugField::new('slug', 'URL de la page (Slug)')
                ->setTargetFieldName('titre')
                ->setColumns('col-12 col-md-6')
                ->setHelp('L\'adresse web (ex: <code>metamorphysis.com/a-propos</code>). Se génère tout seul.'),

            FormField::addFieldset('👁️ Affichage & Publication')->setIcon('fas fa-eye'),

            BooleanField::new('isPublished', 'Publier la page (Mettre en ligne)')
                ->setColumns('col-12 col-md-4')
                ->setHelp('<b>Coché = En ligne pour le public</b>. <b>Décoché = Mode Brouillon</b>.'),

            BooleanField::new('afficherMenu', 'Afficher dans le menu du haut')
                ->setColumns('col-12 col-md-4')
                ->setHelp('<b>Coché = Ajouter le lien dans la barre de navigation du haut (Navbar)</b>.'),

            BooleanField::new('afficherTitre', 'Afficher le titre en haut de page')
                ->setColumns('col-12 col-md-4')
                ->setHelp('Décochez pour la page d\'Accueil.'),

            TextareaField::new('metaDescription', 'Description pour Google (SEO)')
                ->setColumns(12)
                ->setHelp('💡 <b>Astuce référencement :</b> Rédigez 1 à 2 phrases courtes (max 150 caractères) décrivant le contenu de cette page. C\'est ce résumé qui s\'affichera sur Google !')
                ->setRequired(false),

            FormField::addFieldset('🧱 Constructeur de Blocs de Contenu (Page Builder)')->setIcon('fas fa-layer-group'),

            CollectionField::new('sections', 'Blocs de la page')
                ->useEntryCrudForm(SectionCrudController::class)
                ->setHelp('💡 <b>Comment composer votre page ?</b> Cliquez sur "Ajouter un élément" ci-dessous. Chaque bloc peut être un texte centré, une photo avec texte, un carrousel de prestations ou un parcours d\'étapes. Renseignez la "Position" (1, 2, 3...) dans chaque bloc pour choisir quel bloc passe en haut ou en bas.')
        ];
    }
}