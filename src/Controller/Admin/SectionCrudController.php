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
            // 1. CONFIGURATION GÉNÉRALE DU BLOC
            // ======================================================
            FormField::addFieldset('⚙️ Disposition & Couleur de Fond')
                ->setIcon('fas fa-cogs')
                ->setCssClass('builder-fieldset-general'),
            
            IntegerField::new('ordre', 'Position du bloc dans la page (1, 2, 3...)')
                ->setColumns(12)
                ->setHelp('💡 <code>1</code> = tout en haut, <code>2</code> = second bloc, <code>3</code> = troisième bloc...'),
            
            ChoiceField::new('disposition', 'Modèle de mise en page (Gabarit)')
                ->setColumns(12)
                ->setChoices([
                    '📄 Texte Centré'                                           => 'texte_centre',
                    '🖼️ Image à Gauche + Texte à Droite'                         => 'img_gauche',
                    '🖼️ Texte à Gauche + Image à Droite'                         => 'img_droite',
                    '🖼️ Image au Centre + Textes'                                => 'img_centre',
                    '📊 Grille Multi-Colonnes (2 à 5 colonnes avec icônes)'      => 'grille_colonnes',
                    '📦 Rangée Flexible / Conteneur Horizontal (Flexbox Row)'    => 'flex_row',
                    '🌅 Bannière Pleine Largeur'                                  => 'banniere',
                    '🎠 Carrousel des Prestations'                              => 'slider_prestations',
                    '🌸 Bandeau Signature & Logo M'                             => 'bandeau_conclusion',
                    '🌸 Bloc Info Pratique'                                     => 'info_pratique',
                ])
                ->setHelp('Choisissez le modèle visuel de ce bloc sur votre page.')
                ->setRequired(true)->setEmptyData('texte_centre')->renderExpanded(false),
            
            ChoiceField::new('couleurFond', 'Couleur de fond du bloc')
                ->setColumns(12)
                ->setChoices([
                    '🍷 Pourpre Sombre (Fond: #2A1A1E - Fond standard du site)'       => 'plum',
                    '🌿 Vert Sauge / Olive (Fond: #727763 - Fond vert de mise en avant)' => 'olive',
                ])
                ->setHelp('💡 Sélection de la teinte de fond du bloc dans la charte graphique.')
                ->renderExpanded(false),

            IntegerField::new('decalagePosY', '↕️ Chevauchement / Décalage vertical du bloc (px)')
                ->setColumns(12)
                ->setHelp('💡 <code>0</code> = alignement standard. Mettez un nombre négatif (ex: <code>-60</code>) pour faire monter ce bloc sur le bloc du dessus.')->hideOnIndex(),

            // ======================================================
            // 2. CONTENU TEXTUEL
            // ======================================================
            FormField::addFieldset('📝 Textes & Paragraphes')
                ->setIcon('fas fa-align-left')
                ->setCssClass('builder-fieldset-texte'),

            TextField::new('titre', 'Titre de la section (ex: L\'EXPÉRIENCE)')
                ->setColumns(12)
                ->setCssClass('builder-field-titre')
                ->setHelp('Le titre principal affiché en grand.')
                ->setRequired(false),

            ChoiceField::new('titreCouleur', '🎨 Couleur du titre')
                ->setColumns(12)
                ->setCssClass('builder-field-titreCouleur')
                ->setChoices([
                    '🔱 Or Bruni / Ambré (#9C804F - Recommandé)' => 'gold-hover',
                    '✨ Or Antique (#B89A63 - Doré Métamorphysis)' => 'gold',
                    '📜 Ivoire Délicat (#D8D0BE - Blanc Cassé)'    => 'ivory',
                    '🌿 Vert Sauge (#727763 - Vert Doux)'          => 'sage',
                    '🫒 Vert Olive (#4A4F41 - Vert Profond)'       => 'olive',
                    '🍷 Pourpre Sombre (#2A1A1E - Bordeaux)'       => 'plum',
                    '⬛ Noir Charbon (#0A0A09)'                    => 'black',
                ])
                ->setRequired(false)
                ->setEmptyData('gold-hover')
                ->setHelp('Choisissez la teinte de votre grand titre.'),

            ChoiceField::new('titreLigneDecor', '✨ Trait doré décoratif autour du titre')
                ->setColumns(12)
                ->setCssClass('builder-field-titreLigneDecor')
                ->setChoices([
                    '➡️ Ligne dorée après le titre (ex: LE PROCESSUS ────────)' => 'apres',
                    '⬅️ Ligne dorée avant le titre (ex: ──────── LE PROCESSUS)' => 'avant',
                    '↔️ Lignes dorées avant ET après (ex: ──── LE PROCESSUS ────)' => 'avant_apres',
                    '🚫 Aucun trait décoratif (Titre simple épuré)' => 'none',
                ])
                ->setRequired(false)
                ->setEmptyData('apres')
                ->setHelp('Personnalisez la disposition de la ligne dorée décorative à côté de votre titre.'),

            TextareaField::new('sousTitre', 'Sous-titre / Phrase d\'accroche (Retour à la ligne possible)')
                ->setColumns(12)
                ->setCssClass('builder-field-sousTitre')
                ->setNumOfRows(2)
                ->setHelp('Optionnel : Appuyez sur Entrée pour écrire sur 2 lignes (ex : ligne 1 en grec, ligne 2 en français).')
                ->setRequired(false),

            ChoiceField::new('sousTitreCouleur', '🎨 Couleur du sous-titre')
                ->setColumns(12)
                ->setCssClass('builder-field-sousTitreCouleur')
                ->setChoices([
                    '📜 Ivoire Délicat (#D8D0BE - Recommandé)'     => 'ivory',
                    '🔱 Or Bruni / Ambré (#9C804F)'                => 'gold-hover',
                    '✨ Or Antique (#B89A63 - Doré Métamorphysis)' => 'gold',
                    '🌿 Vert Sauge (#727763 - Vert Doux)'          => 'sage',
                    '🫒 Vert Olive (#4A4F41 - Vert Profond)'       => 'olive',
                    '🍷 Pourpre Sombre (#2A1A1E - Bordeaux)'       => 'plum',
                    '⬛ Noir Charbon (#0A0A09)'                    => 'black',
                ])
                ->setRequired(false)
                ->setEmptyData('ivory')
                ->setHelp('Choisissez la teinte de votre sous-titre.'),

            ChoiceField::new('baliseHtml', 'Taille du titre')
                ->setColumns(12)
                ->setCssClass('builder-field-baliseHtml')
                ->setChoices([
                    'Grand Titre (H2 - Recommandé)' => 'h2', 
                    'Moyen (H3)'                    => 'h3', 
                    'Petit (H4)'                    => 'h4'
                ])
                ->setRequired(false)->setEmptyData('h2')->hideOnIndex(),
            
            TextareaField::new('contenu', 'Texte & Paragraphes')
                ->setColumns(12)
                ->setCssClass('builder-field-contenu')
                ->setNumOfRows(6)
                ->setRequired(false)
                ->setHelp('Rédigez ici le contenu textuel de cette section.'),

            ChoiceField::new('texteCouleur', '🎨 Couleur du texte & description des colonnes')
                ->setColumns(12)
                ->setCssClass('builder-field-texteCouleur')
                ->setChoices([
                    '📜 Ivoire Délicat (#D8D0BE - Recommandé)'     => 'ivory',
                    '🔱 Or Bruni / Ambré (#9C804F)'                => 'gold-hover',
                    '✨ Or Antique (#B89A63 - Doré Métamorphysis)' => 'gold',
                    '🌿 Vert Sauge (#727763 - Vert Doux)'          => 'sage',
                    '🫒 Vert Olive (#4A4F41 - Vert Profond)'       => 'olive',
                    '🍷 Pourpre Sombre (#2A1A1E - Bordeaux)'       => 'plum',
                    '⬛ Noir Charbon (#0A0A09)'                    => 'black',
                ])
                ->setRequired(false)
                ->setEmptyData('ivory')
                ->setHelp('Choisissez la teinte de vos paragraphes ou des textes de vos colonnes.'),

            BooleanField::new('texteGras', '💪 Renforcer l\'écriture en Gras')
                ->setColumns(12)
                ->setCssClass('builder-field-texteGras')
                ->setHelp('<b>Coché = Écriture plus grasse pour une lisibilité maximale</b>. Décoché = Écriture élégante fine.')
                ->hideOnIndex(),

            // ======================================================
            // 3. BOUTON D'ACTION & REDIRECTION (CTA)
            // ======================================================
            FormField::addFieldset('🔘 Bouton d\'Action & Redirection (Optionnel)')
                ->setIcon('fas fa-link')
                ->setCssClass('builder-fieldset-cta'),

            TextField::new('boutonTexte', 'Texte sur le bouton (ex: Réserver un soin, En savoir plus)')
                ->setColumns(12)
                ->setHelp('Laissez vide si vous ne souhaitez pas afficher de bouton.')
                ->setRequired(false),

            TextField::new('boutonLien', 'Adresse du lien de redirection (URL ou Page)')
                ->setColumns(12)
                ->setHelp('Ex: <code>/reserver/1</code>, <code>/a-propos</code>, ou <code>https://...</code>.')
                ->setRequired(false),

            ChoiceField::new('boutonStyle', 'Style visuel du Bouton')
                ->setColumns(12)
                ->setChoices([
                    '🔱 Doré Premium (Fond: Or | Texte: Noir | Survol: Or Foncé)' => 'gold',
                    '🔲 Contour Épuré (Fond: Transparent | Bordure: Or | Survol: Plein Or)' => 'outline',
                    '🌿 Vert Sauge (Fond: Sauge | Texte: Ivoire | Survol: Olive)' => 'sage',
                    '🍷 Pourpre Sombre (Fond: Pourpre | Texte: Or | Survol: Plein Or)' => 'plum',
                ])
                ->setHelp('Définit les couleurs du bouton.')
                ->setRequired(false)->setEmptyData('gold')->hideOnIndex(),

            ChoiceField::new('boutonCible', 'Ouverture du lien')
                ->setColumns(12)
                ->setChoices([
                    'Dans le même onglet' => '_self',
                    'Dans un nouvel onglet' => '_blank',
                ])
                ->setRequired(false)->setEmptyData('_self')->hideOnIndex(),

            // ======================================================
            // 4. PHOTO & RÉGLAGES VISUELS (VULGARISÉS)
            // ======================================================
            FormField::addFieldset('🖼️ Photo Principale & Réglages Visuels')
                ->setIcon('fas fa-image')
                ->setCssClass('builder-fieldset-image'),
            
            Field::new('imageFile', 'Téléverser votre photo depuis votre ordinateur')
                ->setColumns(12)
                ->setFormType(VichImageType::class)->setRequired(false)->hideOnIndex()
                ->setHelp('💡 <b>Format conseillé :</b> JPG ou WebP, taille max 1200x800 px, poids < 1 Mo.'),

            TextField::new('media', '📁 OU Nom d\'une image existante (ex: fauteuils.jpg, soin.webp)')
                ->setColumns(12)
                ->setHelp('💡 Si votre image est déjà sur le site (ex: <code>fauteuils.jpg</code>), vous pouvez simplement taper son nom ici sans devoir la re-téléverser.')
                ->setRequired(false),

            TextField::new('imageLien', '🔗 Rendre la photo cliquable (Adresse URL optionnelle)')
                ->setColumns(12)
                ->setHelp('Ex: <code>/a-propos</code> ou <code>https://...</code>.')
                ->setRequired(false),

            ChoiceField::new('imageCadreCouleur', '🖼️ Couleur du cadre décoratif autour de la photo')
                ->setColumns(12)
                ->setChoices([
                    '🍷 Pourpre Sombre (#2A1A1E - Recommandé)'   => 'plum',
                    '🔱 Or Bruni / Ambré (#9C804F)'                => 'gold-hover',
                    '✨ Or Antique (#B89A63 - Doré Métamorphysis)' => 'gold',
                    '🌿 Vert Sauge (#727763 - Vert Doux)'          => 'sage',
                    '🫒 Vert Olive (#4A4F41 - Vert Profond)'       => 'olive',
                    '📜 Ivoire Délicat (#D8D0BE - Blanc Cassé)'    => 'ivory',
                    '⬛ Noir Charbon (#0A0A09)'                    => 'black',
                    '🚫 Aucun cadre'                               => 'none',
                ])
                ->setHelp('Choisissez la couleur du cadre autour de votre photo.')
                ->setRequired(false)->setEmptyData('plum')->hideOnIndex(),

            IntegerField::new('imageCadreHaut', '⬆️ Bord Haut')
                ->setColumns('col-12 col-md-6')
                ->hideOnIndex(),

            IntegerField::new('imageCadreBas', '⬇️ Bord Bas')
                ->setColumns('col-12 col-md-6')
                ->hideOnIndex(),

            IntegerField::new('imageCadreGauche', '⬅️ Bord Gauche')
                ->setColumns('col-12 col-md-6')
                ->hideOnIndex(),

            IntegerField::new('imageCadreDroite', '➡️ Bord Droite')
                ->setColumns('col-12 col-md-6')
                ->setHelp('💡 Ex: 40 pour créer une bande latérale colorée')
                ->hideOnIndex(),
                
            IntegerField::new('imagePosX', '↔️ Déplacer la photo vers la gauche ou la droite (%)')
                ->setColumns(12)
                ->setHelp('💡 <code>0</code> = centré. Nombre positif (ex: <code>10</code>) = vers la droite, négatif (ex: <code>-10</code>) = vers la gauche.')->hideOnIndex(),
                
            IntegerField::new('imagePosY', '↕️ Faire monter ou descendre la photo (px)')
                ->setColumns(12)
                ->setHelp('💡 <code>0</code> = aligné. Positif (ex: <code>40</code>) = descend, négatif (ex: <code>-40</code>) = monte pour chevaucher.')->hideOnIndex(),

            ChoiceField::new('imageSuperposition', '🔀 Effet de superposition (Image & Texte)')
                ->setColumns(12)
                ->setChoices([
                    'Standard (Côte à côte sans superposition)' => 'standard',
                    '🖼️ Photo PAR-DESSUS le Texte'              => 'image_sur_texte',
                    '📝 Texte PAR-DESSUS la Photo'              => 'texte_sur_image',
                ])->setRequired(false)->setEmptyData('standard')->hideOnIndex(),

            IntegerField::new('cropHaut', '✂️ Recadrer le Haut de la photo (%)')
                ->setColumns(12)
                ->setHelp('Pourcentage à supprimer en haut (ex: <code>10</code> pour 10%).')->hideOnIndex(),

            IntegerField::new('cropBas', '✂️ Recadrer le Bas de la photo (%)')
                ->setColumns(12)
                ->setHelp('Pourcentage à supprimer en bas.')->hideOnIndex(),

            IntegerField::new('cropGauche', '✂️ Recadrer la Gauche de la photo (%)')
                ->setColumns(12)
                ->setHelp('Pourcentage à supprimer à gauche.')->hideOnIndex(),

            IntegerField::new('cropDroite', '✂️ Recadrer la Droite de la photo (%)')
                ->setColumns(12)
                ->setHelp('Pourcentage à supprimer à droite.')->hideOnIndex(),

            IntegerField::new('largeurMedia', '📐 Largeur Maximale sur-mesure (px - optionnel)')
                ->setColumns(12)
                ->setHelp('Laissez vide pour la taille automatique par défaut.')->setRequired(false)->hideOnIndex(),

            IntegerField::new('hauteurMedia', '📐 Hauteur Maximale sur-mesure (px - optionnel)')
                ->setColumns(12)
                ->setHelp('Laissez vide pour le ratio automatique.')->setRequired(false)->hideOnIndex(),

            // ======================================================
            // 5. ENCART CITATION SUPERPOSÉE
            // ======================================================
            FormField::addFieldset('✨ Encart Citation Superposée sur la Photo (Optionnel)')
                ->setIcon('fas fa-quote-right')
                ->setCssClass('builder-fieldset-citation'),

            TextareaField::new('citation', '💬 Texte de la Citation / Phrase d\'Accroche')
                ->setColumns(12)
                ->setHelp('Remplissez ce champ si vous souhaitez afficher un encart élégant par-dessus votre photo.')->setRequired(false),

            IntegerField::new('citationHauteurMax', '📜 Hauteur Max de l\'encart avant défilement (px - optionnel)')
                ->setColumns(12)
                ->setHelp('Ex: <code>300</code> pour faire défiler un long texte, ou laissez vide.')->hideOnIndex(),

            IntegerField::new('citationLargeur', 'Largeur de l\'encart (%)')
                ->setColumns(12)
                ->setHelp('Ex: <code>90</code>% (recommandé pour une belle lisibilité).')->hideOnIndex(),

            IntegerField::new('citationPosX', '↔️ Décalage Horizontal de l\'encart (%)')
                ->setColumns(12)
                ->setHelp('Ex: <code>-10</code> (déborde joliment à gauche de la photo).')->hideOnIndex(),
                
            IntegerField::new('citationPosY', '↕️ Chevauchement Vertical sur la Photo (px)')
                ->setColumns(12)
                ->setHelp('💡 Mettez <code>-150</code> pour faire monter la citation sur la photo, ou <code>0</code> pour aligner en bas.')->hideOnIndex(),

            ChoiceField::new('citationCouleurFond', 'Couleur de Fond de l\'encart')
                ->setColumns(12)
                ->setChoices([
                    '🍷 Pourpre Sombre (#2A1A1E)'       => 'meta-plum', 
                    '🌿 Vert Sauge / Olive (#727763)'    => 'meta-olive',
                    '⬛ Noir Profond (#0A0A09)'          => 'meta-black', 
                    '🔱 Or Antique (#B89A63)'            => 'meta-gold', 
                    '📜 Ivoire Délicat (#D8D0BE)'        => 'meta-ivory',
                ])->hideOnIndex(),

            ChoiceField::new('citationCouleurTexte', 'Couleur du Texte de l\'encart')
                ->setColumns(12)
                ->setChoices([
                    '📜 Ivoire Délicat (#D8D0BE - Recommandé)' => 'meta-ivory',
                    '🔱 Or Antique (#B89A63 - Écriture dorée)'  => 'meta-gold',
                    '⬛ Noir Profond (#0A0A09)'                  => 'meta-black', 
                    '🍷 Pourpre Sombre (#2A1A1E)'                => 'meta-plum', 
                    '🌿 Vert Sauge (#727763)'                    => 'meta-sage',
                ])->hideOnIndex(),

            // ======================================================
            // 6. COLONNES CÔTE À CÔTE (POUR GRILLE MULTI-COLONNES)
            // ======================================================
            FormField::addFieldset('📊 Colonnes Côte à Côte (Multi-Colonnes)')
                ->setIcon('fas fa-columns')
                ->setCssClass('builder-fieldset-colonnes'),

            CollectionField::new('etapes', '📊 Liste des Colonnes')
                ->setColumns(12)
                ->setHelp('💡 <b>Pour ajouter une colonne :</b> Cliquez sur "Ajouter un nouvel élément" ci-dessous (Icône, Titre, Texte).')
                ->useEntryCrudForm(EtapeCrudController::class)->hideOnIndex(),

            // ======================================================
            // 7. CARROUSEL DES PRESTATIONS
            // ======================================================
            FormField::addFieldset('🎠 Carrousel des Prestations')
                ->setIcon('fas fa-gem')
                ->setCssClass('builder-fieldset-prestations'),

            AssociationField::new('prestations', 'Prestations à inclure dans le Carrousel')
                ->setColumns(12)
                ->setHelp('Sélectionnez les prestations à afficher.')
                ->setFormTypeOptions(['by_reference' => false])->hideOnIndex(),
        ];
    }
}