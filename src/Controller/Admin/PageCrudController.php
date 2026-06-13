<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;


class PageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('titre', 'Titre de la page'),
            BooleanField::new('isPublished', 'Publier la page (En ligne)')
                ->setHelp('Si décoché, la page restera en mode Brouillon (invisible pour le public).'),
            
            SlugField::new('slug', 'URL de la page')
                ->setTargetFieldName('titre')
                ->hideOnIndex(),
            TextareaField::new('metaDescription', 'Description SEO (Google)')
                            ->setHelp('Texte de 150 caractères max résumant la page pour les moteurs de recherche.')
                            ->setRequired(false),               
            BooleanField::new('afficherMenu', 'Afficher dans la barre de navigation')
             ->setHelp('Cochez cette case pour que le lien apparaisse en haut du site.'),
            
            // LA MAGIE EST ICI : On intègre le formulaire des Sections dans la Page !
            CollectionField::new('sections', 'Constructeur de blocs')
                ->useEntryCrudForm(SectionCrudController::class)
                ->setHelp('Ajoutez différents blocs pour construire le design de votre page.')
        ];
    }
}