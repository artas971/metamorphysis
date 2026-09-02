<?php

namespace App\Controller\Admin;

use App\Controller\Admin\EtapeCrudController;
use App\Entity\Section;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use Vich\UploaderBundle\Form\Type\VichImageType;

class SectionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Section::class;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile('css/admin_builder.css')
            ->addJsFile('js/admin_builder.js');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // ======================================================
            // 1. MODÈLE & POSITION DU BLOC
            // ======================================================
            FormField::addFieldset('1️⃣ ⚙️ Modèle & Position du Bloc')
                ->setIcon('fas fa-cogs')
                ->setCssClass('builder-fieldset-general')
                ->setHelp('Choisissez le gabarit de ce bloc et sa position dans la page.'),
            
            IntegerField::new('ordre', '🔢 Position du bloc dans la page (1, 2, 3...)')
                ->setColumns(12)
                ->setHelp('💡 <code>1</code> = tout en haut, <code>2</code> = second bloc, <code>3</code> = troisième bloc...'),
            
            ChoiceField::new('disposition', '📐 Modèle de mise en page (Gabarit)')
                ->setColumns(12)
                ->setChoices([
                    '📄 Texte Centré'                                 => 'texte_centre',
                    '🖼️ Texte à Gauche + Image à Droite'              => 'img_droite',
                    '🖼️ Image à Gauche + Texte à Droite'              => 'img_gauche',
                    '🖼️ Image au Centre + Textes'                     => 'img_centre',
                    '👑 Présentation Expert & Portrait'                => 'presentation_expert',
                    '📊 Grille Multi-Colonnes (2 à 5 colonnes)'       => 'grille_colonnes',
                    '📦 Rangée Flexible'                              => 'flex_row',
                    '⚖️ Grille Mentions Légales'                      => 'grille_mentions',
                    '🌅 Bannière Pleine Largeur'                       => 'banniere',
                    '🎠 Carrousel des Prestations'                    => 'slider_prestations',
                    '🌸 Bandeau Signature & Logo M'                   => 'bandeau_conclusion',
                    '🌸 Bloc Info Pratique'                           => 'info_pratique',
                ])
                ->setHelp('Quel gabarit visuel souhaitez-vous appliquer à ce bloc ?')
                ->setRequired(true)->setEmptyData('texte_centre')->renderExpanded(false),
            
            ChoiceField::new('couleurFond', '🎨 Couleur d\'Arrière-Plan du bloc')
                ->setColumns(12)
                ->setChoices([
                    '🍷 Pourpre Transparent (Recommandé)' => 'plum',
                    '🟩 Vert Olive'                       => 'olive',
                    '⬛ Noir Charbon'                     => 'black',
                    '🌿 Vert Sauge'                       => 'sage',
                    '🚫 Transparent'                      => 'none',
                ])
                ->setHelp('💡 "Pourpre Transparent" préserve le superbe dégradé radial continu.')
                ->renderExpanded(false),

            // ======================================================
            // 2. MARGES, HAUTEUR & ESPACEMENTS (1 CHAMP PAR LIGNE)
            // ======================================================
            FormField::addFieldset('2️⃣ 📐 Marges, Longueur & Espacements du Bloc')
                ->setIcon('fas fa-arrows-alt')
                ->setCssClass('builder-fieldset-espacement')
                ->setHelp('✨ <b>Réglez ici la hauteur du bloc et ses marges</b> extérieures et intérieures.'),

            IntegerField::new('hauteurMin', '📏 Longueur / Hauteur minimale du bloc (px)')
                ->setColumns(12)
                ->setCssClass('builder-field-hauteurMin')
                ->setHelp('💡 <b>Pour allonger la hauteur du bloc :</b> entrez une valeur (ex: <code>500</code>, <code>650</code>, <code>800</code>). Laissez vide pour la taille automatique.')
                ->setRequired(false)
                ->hideOnIndex(),

            IntegerField::new('margeHaut', '↕️ Marge Extérieure HAUTE (px) - Espacer du haut ou du menu')
                ->setColumns(12)
                ->setCssClass('builder-field-margeHaut')
                ->setHelp('💡 Déplace tout le bloc vers le bas (ex: <code>40</code>, <code>80</code>). <code>0</code> = collé au haut.')
                ->setEmptyData(0)->hideOnIndex(),

            IntegerField::new('margeBas', '↕️ Marge Extérieure BASSE (px) - Espacer du bloc suivant')
                ->setColumns(12)
                ->setCssClass('builder-field-margeBas')
                ->setHelp('💡 Crée un espace sous ce bloc (ex: <code>40</code>, <code>80</code>). <code>0</code> = aucun espace.')
                ->setEmptyData(0)->hideOnIndex(),

            IntegerField::new('paddingHaut', '⬆️ Marge Intérieure Haute (px) - Espace au-dessus du texte')
                ->setColumns(12)
                ->setCssClass('builder-field-paddingHaut')
                ->setHelp('Espace intérieur en haut dans le bloc (défaut : 48px)')
                ->setEmptyData(48)->hideOnIndex(),

            IntegerField::new('paddingBas', '⬇️ Marge Intérieure Basse (px) - Espace en-dessous du texte')
                ->setColumns(12)
                ->setCssClass('builder-field-paddingBas')
                ->setHelp('Espace intérieur en bas dans le bloc (défaut : 48px)')
                ->setEmptyData(48)->hideOnIndex(),

            IntegerField::new('paddingGauche', '⬅️ Marge Intérieure Gauche (px)')
                ->setColumns(12)
                ->setCssClass('builder-field-paddingGauche')
                ->setHelp('Espace intérieur à gauche (défaut : 48px)')
                ->setEmptyData(48)->hideOnIndex(),

            IntegerField::new('paddingDroite', '➡️ Marge Intérieure Droite (px)')
                ->setColumns(12)
                ->setCssClass('builder-field-paddingDroite')
                ->setHelp('💡 <b>0</b> = collé à l\'extrémité droite de l\'écran')
                ->setEmptyData(48)->hideOnIndex(),

            IntegerField::new('decalagePosY', '↕️ Chevauchement vertical spécial (px)')
                ->setColumns(12)
                ->setHelp('💡 <code>0</code> = alignement normal. Nombre négatif (ex: <code>-60</code>) pour faire monter ce bloc sur le bloc du dessus.')
                ->hideOnIndex(),

            // ======================================================
            // 3. CONTENU TEXTUEL DU BLOC (1 CHAMP PAR LIGNE)
            // ======================================================
            FormField::addFieldset('3️⃣ 📝 Textes, Titres & Paragraphes')
                ->setIcon('fas fa-align-left')
                ->setCssClass('builder-fieldset-texte'),

            TextField::new('titre', 'Titre de la section (ex: L\'EXPÉRIENCE)')
                ->setColumns(12)
                ->setCssClass('builder-field-titre')
                ->setHelp('Titre principal affiché en grand.')
                ->setRequired(false),

            ChoiceField::new('titreCouleur', '🎨 Couleur du titre')
                ->setColumns(12)
                ->setCssClass('builder-field-titreCouleur')
                ->setChoices([
                    '🔱 Or Bruni'        => 'gold-hover',
                    '✨ Or Antique'      => 'gold',
                    '📜 Ivoire'          => 'ivory',
                    '🌿 Vert Sauge'      => 'sage',
                    '🟩 Vert Olive'      => 'olive',
                    '🍷 Pourpre Sombre'  => 'plum',
                    '⬛ Noir Charbon'    => 'black',
                ])
                ->setRequired(false)
                ->setEmptyData('gold-hover')
                ->setHelp('Teinte du grand titre.'),

            ChoiceField::new('titreLigneDecor', '✨ Trait doré décoratif autour du titre')
                ->setColumns(12)
                ->setCssClass('builder-field-titreLigneDecor')
                ->setChoices([
                    '➡️ Ligne dorée après le titre'        => 'apres',
                    '⬅️ Ligne dorée avant le titre'        => 'avant',
                    '↔️ Lignes dorées avant et après'      => 'avant_apres',
                    '🚫 Aucun trait décoratif'             => 'none',
                ])
                ->setRequired(false)
                ->setEmptyData('apres')
                ->setHelp('Ligne dorée décorative à côté du titre.'),

            TextareaField::new('sousTitre', 'Sous-titre / Phrase d\'accroche (Optionnel)')
                ->setColumns(12)
                ->setCssClass('builder-field-sousTitre')
                ->setNumOfRows(2)
                ->setHelp('Appuyez sur Entrée pour écrire sur 2 lignes si besoin.')
                ->setRequired(false),

            ChoiceField::new('sousTitreCouleur', '🎨 Couleur du sous-titre')
                ->setColumns(12)
                ->setCssClass('builder-field-sousTitreCouleur')
                ->setChoices([
                    '📜 Ivoire'          => 'ivory',
                    '🔱 Or Bruni'        => 'gold-hover',
                    '✨ Or Antique'      => 'gold',
                    '🌿 Vert Sauge'      => 'sage',
                    '🟩 Vert Olive'      => 'olive',
                    '🍷 Pourpre Sombre'  => 'plum',
                    '⬛ Noir Charbon'    => 'black',
                ])
                ->setRequired(false)
                ->setEmptyData('ivory')
                ->setHelp('Teinte du sous-titre.'),

            ChoiceField::new('baliseHtml', 'Taille du titre')
                ->setColumns(12)
                ->setCssClass('builder-field-baliseHtml')
                ->setChoices([
                    'Grand Titre (H2)' => 'h2', 
                    'Moyen (H3)'       => 'h3', 
                    'Petit (H4)'       => 'h4'
                ])
                ->setRequired(false)->setEmptyData('h2')->hideOnIndex(),
            
            TextareaField::new('contenu', 'Texte & Paragraphes (Sauts de ligne automatiques)')
                ->setColumns(12)
                ->setCssClass('builder-field-contenu')
                ->setNumOfRows(5)
                ->setRequired(false)
                ->setHelp('Rédigez votre texte librement. Un appui sur Entrée crée automatiquement un paragraphe.'),

            ChoiceField::new('texteCouleur', '🎨 Couleur du texte')
                ->setColumns(12)
                ->setCssClass('builder-field-texteCouleur')
                ->setChoices([
                    '📜 Ivoire'          => 'ivory',
                    '🔱 Or Bruni'        => 'gold-hover',
                    '✨ Or Antique'      => 'gold',
                    '🌿 Vert Sauge'      => 'sage',
                    '🟩 Vert Olive'      => 'olive',
                    '🍷 Pourpre Sombre'  => 'plum',
                    '⬛ Noir Charbon'    => 'black',
                ])
                ->setRequired(false)
                ->setEmptyData('ivory')
                ->setHelp('Teinte de vos paragraphes.'),

            ChoiceField::new('alignementTexte', '↔️ Alignement du texte')
                ->setColumns(12)
                ->setCssClass('builder-field-alignementTexte')
                ->setChoices([
                    '🔘 Centré au milieu (Recommandé)' => 'center',
                    '⬅️ Aligné à Gauche'             => 'start',
                    '➡️ Aligné à Droite'             => 'end',
                ])
                ->setRequired(false)
                ->setEmptyData('center')
                ->setHelp('Position du texte dans le bloc.')
                ->hideOnIndex(),

            BooleanField::new('texteGras', '💪 Écriture en Gras')
                ->setColumns(12)
                ->setCssClass('builder-field-texteGras')
                ->setHelp('<b>Coché = Écriture grasse</b>. Décoché = Écriture fine.')
                ->hideOnIndex(),

            // ======================================================
            // 4. PHOTO & CADRE DÉCORATIF (1 CHAMP PAR LIGNE)
            // ======================================================
            FormField::addFieldset('4️⃣ 🖼️ Photo Principale & Cadre Décoratif')
                ->setIcon('fas fa-image')
                ->setCssClass('builder-fieldset-image'),
            
            Field::new('imageFile', 'Téléverser votre photo depuis votre ordinateur')
                ->setColumns(12)
                ->setFormType(VichImageType::class)->setRequired(false)->hideOnIndex()
                ->setHelp('💡 Format JPG ou WebP.'),

            TextField::new('media', '📁 OU Nom de l\'image existante (ex: vase.jpg, salon.webp)')
                ->setColumns(12)
                ->setHelp('Nom de fichier déjà présent dans le dossier uploads.')
                ->setRequired(false),

            TextField::new('imageLien', '🔗 Lien sur la photo (Optionnel)')
                ->setColumns(12)
                ->setHelp('Ex: <code>/a-propos</code> ou <code>https://...</code>.')
                ->setRequired(false),

            ChoiceField::new('imageCadreCouleur', '🖼️ Couleur du cadre décoratif autour de la photo')
                ->setColumns(12)
                ->setChoices([
                    '✨ Or Antique (Recommandé)' => 'gold',
                    '🟩 Vert Olive'             => 'olive',
                    '🌿 Vert Sauge'             => 'sage',
                    '🔱 Or Bruni'               => 'gold-hover',
                    '📜 Ivoire'                 => 'ivory',
                    '🍷 Pourpre Sombre'         => 'plum',
                    '⬛ Noir Charbon'           => 'black',
                    '🚫 Aucun cadre'            => 'none',
                ])
                ->setHelp('Couleur du cadre ou bande latérale décorative.')
                ->setRequired(false)->setEmptyData('gold')->hideOnIndex(),

            IntegerField::new('imageCadreHaut', '⬆️ Épaisseur Bord Haut (px)')
                ->setColumns(12)
                ->setHelp('0 = aucun')
                ->hideOnIndex(),

            IntegerField::new('imageCadreBas', '⬇️ Épaisseur Bord Bas (px)')
                ->setColumns(12)
                ->setHelp('0 = aucun')
                ->hideOnIndex(),

            IntegerField::new('imageCadreGauche', '⬅️ Épaisseur Bord Gauche (px)')
                ->setColumns(12)
                ->setHelp('Ex: 40 (bande latérale gauche)')
                ->hideOnIndex(),

            IntegerField::new('imageCadreDroite', '➡️ Épaisseur Bord Droite (px)')
                ->setColumns(12)
                ->setHelp('Ex: 40 (bande latérale droite)')
                ->hideOnIndex(),

            // ======================================================
            // 5. BOUTON D'ACTION & REDIRECTION (CTA)
            // ======================================================
            FormField::addFieldset('5️⃣ 🔘 Bouton d\'Action (Optionnel)')
                ->setIcon('fas fa-link')
                ->setCssClass('builder-fieldset-cta'),

            TextField::new('boutonTexte', 'Texte sur le bouton (ex: Réserver un soin, Découvrir)')
                ->setColumns(12)
                ->setHelp('Laissez vide pour ne pas afficher de bouton.')
                ->setRequired(false),

            TextField::new('boutonLien', 'Adresse du lien de redirection')
                ->setColumns(12)
                ->setHelp('Ex: <code>/lexperience</code> ou <code>/contact</code>.')
                ->setRequired(false),

            ChoiceField::new('boutonStyle', 'Style visuel du Bouton')
                ->setColumns(12)
                ->setChoices([
                    '🔱 Doré'              => 'gold',
                    '🔲 Contour Doré'      => 'outline',
                    '🌿 Vert Sauge'        => 'sage',
                    '🍷 Pourpre Sombre'    => 'plum',
                ])
                ->setHelp('Style du bouton.')
                ->setRequired(false)->setEmptyData('gold')->hideOnIndex(),

            ChoiceField::new('boutonCible', 'Ouverture du lien')
                ->setColumns(12)
                ->setChoices([
                    'Dans le même onglet' => '_self',
                    'Dans un nouvel onglet' => '_blank',
                ])
                ->setRequired(false)->setEmptyData('_self')->hideOnIndex(),

            // ======================================================
            // 6. COLONNES CÔTE À CÔTE (GRILLE MULTI-COLONNES)
            // ======================================================
            FormField::addFieldset('6️⃣ 📊 Colonnes Côte à Côte (Multi-Colonnes)')
                ->setIcon('fas fa-columns')
                ->setCssClass('builder-fieldset-colonnes'),

            CollectionField::new('etapes', '📊 Liste des Colonnes')
                ->setColumns(12)
                ->setHelp('💡 <b>Pour ajouter une colonne :</b> Cliquez sur "Ajouter un nouvel élément" ci-dessous.')
                ->useEntryCrudForm(EtapeCrudController::class)->hideOnIndex(),

            // ======================================================
            // 7. CARROUSEL DES PRESTATIONS
            // ======================================================
            FormField::addFieldset('7️⃣ 🎠 Carrousel des Prestations')
                ->setIcon('fas fa-gem')
                ->setCssClass('builder-fieldset-prestations'),

            AssociationField::new('prestations', 'Prestations à inclure dans le Carrousel')
                ->setColumns(12)
                ->setHelp('Sélectionnez les prestations à afficher.')
                ->setFormTypeOptions(['by_reference' => false])->hideOnIndex(),

            // ======================================================
            // 8. ENCART & CITATION FLOTTANTE (SUR OU SOUS LA PHOTO)
            // ======================================================
            FormField::addFieldset('8️⃣ 💬 Encart & Citation Flottante (Optionnel)')
                ->setIcon('fas fa-quote-left')
                ->setCssClass('builder-fieldset-citation')
                ->setHelp('💡 Affichez un élégant encart de texte ou de citation superposé à votre photo (comme sur la page À Propos).')
                ->collapsible()
                ->renderCollapsed(),

            TextareaField::new('citation', '💬 Texte de la Citation / Encart')
                ->setColumns(12)
                ->setNumOfRows(3)
                ->setHelp('Saisissez le texte de l\'encart qui viendra se placer sur ou à côté de la photo.')
                ->setRequired(false),

            ChoiceField::new('citationCouleurFond', '🎨 Couleur de Fond de l\'encart')
                ->setColumns(12)
                ->setChoices([
                    '🟩 Vert Olive'     => 'meta-olive',
                    '🍷 Pourpre Sombre' => 'meta-plum', 
                    '🌿 Vert Sauge'     => 'meta-sage',
                    '⬛ Noir Profond'   => 'meta-black', 
                    '🔱 Or Antique'     => 'meta-gold', 
                    '📜 Ivoire'         => 'meta-ivory',
                ])
                ->setRequired(false)
                ->setEmptyData('meta-olive')
                ->hideOnIndex(),

            ChoiceField::new('citationCouleurTexte', '🎨 Couleur du Texte de l\'encart')
                ->setColumns(12)
                ->setChoices([
                    '📜 Ivoire (Recommandé)' => 'meta-ivory',
                    '🔱 Or Antique'          => 'meta-gold',
                    '⬛ Noir Profond'        => 'meta-black', 
                    '🍷 Pourpre Sombre'      => 'meta-plum', 
                    '🌿 Vert Sauge'          => 'meta-sage',
                ])
                ->setRequired(false)
                ->setEmptyData('meta-ivory')
                ->hideOnIndex(),

            IntegerField::new('citationPosX', '↔️ Position Horizontale de l\'encart (%)')
                ->setColumns(12)
                ->setHelp('💡 <b>0 = aligné pile sur le bord gauche de la photo</b>. Nombre positif (ex: <code>10</code>) = décalé vers la droite. Nombre négatif (ex: <code>-15</code>) = déborde vers la gauche.')
                ->setRequired(false)
                ->hideOnIndex(),

            IntegerField::new('citationPosY', '↕️ Position Verticale de l\'encart (px)')
                ->setColumns(12)
                ->setHelp('💡 <code>-120</code> = fait remonter l\'encart sur le bas de la photo. <code>0</code> = collé juste sous la photo. <code>40</code> = descend l\'encart.')
                ->setRequired(false)
                ->hideOnIndex(),

            IntegerField::new('citationLargeur', '📏 Largeur de l\'encart (%)')
                ->setColumns(12)
                ->setHelp('💡 <code>85</code> = 85% de la largeur de la photo, <code>100</code> = même largeur que la photo (bord gauche et bord droit alignés).')
                ->setRequired(false)
                ->hideOnIndex(),

            IntegerField::new('citationHauteurMax', '📏 Hauteur Maximale avec défilement fluide (px - Optionnel)')
                ->setColumns(12)
                ->setHelp('💡 Laissez vide pour une hauteur automatique. Si le texte est très long, entrez une hauteur max (ex: <code>250</code>).')
                ->setRequired(false)
                ->hideOnIndex(),

            // ======================================================
            // 9. DÉCALAGES & SUPERPOSITION DE LA PHOTO
            // ======================================================
            FormField::addFieldset('9️⃣ 🛠️ Décalages & Superposition de la Photo')
                ->setIcon('fas fa-sliders-h')
                ->setCssClass('builder-fieldset-image-advanced')
                ->setHelp('Optionnel : ajustez la position précise ou le plan de superposition de la photo.')
                ->collapsible()
                ->renderCollapsed(),

            IntegerField::new('imagePosX', '↔️ Déplacer la photo vers la gauche ou droite (%)')
                ->setColumns(12)
                ->setHelp('0 = centré. Positif = droite, négatif = gauche.')->hideOnIndex(),
                
            IntegerField::new('imagePosY', '↕️ Monter ou descendre la photo (px)')
                ->setColumns(12)
                ->setHelp('0 = aligné. Positif = descend, négatif = monte.')->hideOnIndex(),

            ChoiceField::new('imageSuperposition', '🔀 Superposition Photo / Texte')
                ->setColumns(12)
                ->setChoices([
                    'Standard (Côte à côte sans superposition)' => 'standard',
                    '🖼️ Photo PAR-DESSUS le Texte'              => 'image_sur_texte',
                    '📝 Texte PAR-DESSUS la Photo'              => 'texte_sur_image',
                ])->setRequired(false)->setEmptyData('standard')->hideOnIndex(),
        ];
    }
}