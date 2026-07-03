<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;


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
            ->setEntityLabelInPlural('Pages')
            ->setPageTitle('index', 'Contenu des Pages')
            
            // La bulle d'aide pour l'image_9682da.png
            ->setHelp('index', 'Créez de nouvelles pages ou modifiez l\'agencement et les textes de vos pages actuelles. Utilisez le Page Builder à l\'intérieur de chaque fiche pour structurer vos blocs de contenu.');
    }
    // L'AJOUT EST ICI : La configuration du bouton d'aperçu
    public function configureActions(Actions $actions): Actions
    {
        // On crée une action personnalisée
        $previewAction = Action::new('preview', 'Voir l\'aperçu', 'fa fa-eye')
            ->linkToRoute('app_page_preview', function (Page $page) {
                return ['slug' => $page->getSlug()];
            })
            ->setHtmlAttributes(['target' => '_blank']); // Ouvre dans un nouvel onglet

        return $actions
            // On ajoute le bouton sur la liste des pages
            ->add(Crud::PAGE_INDEX, $previewAction)
            // On ajoute le bouton sur la page de modification
            ->add(Crud::PAGE_EDIT, $previewAction);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('titre', 'Titre de la page'),
            
            BooleanField::new('isPublished', 'Publier la page (En ligne)')
                ->setHelp('Si décoché, la page restera en mode Brouillon (invisible pour le public).'),
            
            SlugField::new('slug', 'URL de la page (Slug)')
                ->setTargetFieldName('titre')
                ->setHelp('Se remplit automatiquement d\'après le titre.'),
                
            TextareaField::new('metaDescription', 'Description SEO (Google)')
                ->setHelp('Texte de 150 caractères max résumant la page pour les moteurs de recherche.')
                ->setRequired(false),               
                
            BooleanField::new('afficherMenu', 'Afficher dans la barre de navigation')
                ->setHelp('Cochez cette case pour que le lien apparaisse en haut du site.'),
            
            // Le CollectionField du Page Builder optimisé
             CollectionField::new('sections', 'Constructeur de blocs (Page Builder)')
                ->useEntryCrudForm(SectionCrudController::class)
                ->setHelp('💡 <b>Astuce :</b> Pour modifier l\'ordre d\'affichage sur le site, ouvrez un bloc, modifiez son numéro de "Position" (1, 2, 3...), puis sauvegardez la page.')
        ];
    }
}