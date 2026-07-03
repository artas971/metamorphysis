<?php

namespace App\Controller\Admin;

use App\Entity\Etape;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class EtapeCrudController extends AbstractCrudController implements ServiceSubscriberInterface
{
    public static function getEntityFqcn(): string
    {
        return Etape::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('titre', 'Titre de l\'étape')
                ->setHelp('Ex: OBSERVER, COMPRENDRE... (Sera affiché en majuscule et en ivoire)'),
                
            TextareaField::new('texte', 'Description')
                ->setNumOfRows(3)
                ->setHelp('Une phrase courte explicative (Couleur sauge).'),
                
            ChoiceField::new('icone', 'Symbole visuel (Icône)')
                ->setChoices([
                    '👁️ Œil (Idéal pour l\'observation)' => 'bi-eye',
                    '🔍 Loupe (Idéal pour la compréhension)' => 'bi-search',
                    '🔄 Flèches (Idéal pour la transformation)' => 'bi-arrow-repeat',
                    '✨ Étoiles (Idéal pour l\'alignement)' => 'bi-stars',
                ])
                ->setHelp('Sélectionnez l\'icône dorée qui apparaîtra dans le cercle noir.'),
        ];
    }
}