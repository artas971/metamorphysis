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
            TextField::new('titre', 'Titre de la Colonne ou Étape')
                ->setColumns(12)
                ->setHelp('Ex: <code>CETTE DÉMARCHE PEUT VOUS CORRESPONDRE SI...</code>'),
                
            TextareaField::new('texte', 'Texte & Description de la Colonne')
                ->setColumns(12)
                ->setNumOfRows(5)
                ->setHelp('Rédigez ici le contenu de cette colonne (vous pouvez mettre des flèches → ou puces).'),
                
            ChoiceField::new('icone', 'Symbole visuel (Icône dorée)')
                ->setColumns(12)
                ->setChoices([
                    '🚫 Aucune icône (Texte brut pur)' => '',
                    '👁️ Œil (Observation)' => 'bi-eye',
                    '🔍 Loupe (Compréhension)' => 'bi-search',
                    '🔄 Flèches (Transformation)' => 'bi-arrow-repeat',
                    '✨ Étoiles (Alignement)' => 'bi-stars',
                    '💎 Diamant' => 'bi-gem',
                    '🌱 Feuille / Nature' => 'bi-flower1',
                ])
                ->setRequired(false)
                ->setHelp('Optionnel : Choisissez une icône si vous souhaitez afficher un cercle doré au-dessus du titre.'),
        ];
    }
}