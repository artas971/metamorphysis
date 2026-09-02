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
                
            ChoiceField::new('icone', 'Symbole visuel (Icône dorée ou SVG)')
                ->setColumns(12)
                ->setChoices([
                    '🚫 Aucune icône (Texte brut pur)' => '',
                    '🦋 Transformer - Libellule Métamorphysis (SVG Officiel Grand Format) ← RECOMMANDÉ' => 'libellule.svg',
                    '👁️ Observer - Œil (bi-eye) ← RECOMMANDÉ' => 'bi-eye',
                    '🧠 Comprendre - Cerveau (SVG Doré) ← RECOMMANDÉ' => 'bi-brain',
                    '📡 Comprendre - Réseau / Esprit (bi-diagram-3)' => 'bi-diagram-3',
                    '💡 Comprendre - Ampoule / Éveil (bi-lightbulb)' => 'bi-lightbulb',
                    '🪞 Confronter - Face-à-Face / Miroir de Vérité (SVG Doré) ← RECOMMANDÉ' => 'bi-symmetry-vertical',
                    '🛡️ Confronter - Bouclier (bi-shield-check)' => 'bi-shield-check',
                    '🌸 Transformer - Libellule / Fleur (bi-flower1)' => 'bi-flower1',
                    '✨ Transformer - Étoiles / Éveil (bi-stars)' => 'bi-stars',
                    '🎯 S\'aligner - Cible / Centre (bi-crosshair) ← RECOMMANDÉ' => 'bi-crosshair',
                    '🧭 S\'aligner - Boussole (bi-compass)' => 'bi-compass',
                    '🔘 S\'aligner - Cible pleine (bi-bullseye)' => 'bi-bullseye',
                    '🔍 Loupe (bi-search)' => 'bi-search',
                    '🔄 Flèches répétition (bi-arrow-repeat)' => 'bi-arrow-repeat',
                    '💎 Diamant (bi-gem)' => 'bi-gem',
                ])
                ->setRequired(false)
                ->setHelp('Choisissez une icône dorée ou le logo libellule à afficher au-dessus du titre.'),
        ];
    }
}