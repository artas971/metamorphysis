<?php

namespace App\Controller\Admin;

use App\Controller\Admin\EtapeCrudController;
use App\Entity\Section;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class SectionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Section::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // ======================================================
            // 1. CONFIGURATION GÉNÉRALE DU BLOC
            // ======================================================
            FormField::addFieldset('⚙️ Disposition & Couleur de Fond')->setIcon('fas fa-cogs'),
            
            IntegerField::new('ordre', 'Ordre d\'affichage du bloc (1, 2, 3...)')
                ->setColumns(12)
                ->setHelp('💡 <code>1</code> = tout en haut de la page, <code>2</code> = second bloc, <code>3</code> = troisième bloc, etc.'),
            
            ChoiceField::new('disposition', 'Design & Disposition du bloc')
                ->setColumns(12)
                ->setChoices([
                    '📄 Texte centré (Classique & Épuré)'               => 'texte_centre',
                    '🖼️ Image à Gauche + Texte à Droite'                => 'img_gauche',
                    '🖼️ Texte à Gauche + Image à Droite'                => 'img_droite',
                    '📊 Grille de Colonnes Côte à Côte (2, 3, 4 ou 5 colonnes avec titre & texte)' => 'grille_colonnes',
                    '🌅 Bannière pleine largeur'                         => 'banniere',
                    '🎦 Carrousel des Prestations (Slider dynamique)'  => 'slider_prestations',
                    '🌸 Bloc Info Pratique (Note & Fleur Libellule)'    => 'info_pratique',
                    '🔄 Cheminement (Parcours en 4 Étapes)'             => 'cheminement',
                    '👩‍💼 Profil Fondatrice (Présentation À Propos)'     => 'presentation_expert',
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

            // ======================================================
            // 2. CONTENU TEXTUEL & BOUTON CTA
            // ======================================================
            FormField::addFieldset('📝 Textes & Contenu')->setIcon('fas fa-align-left'),

            TextField::new('titre', 'Titre de la section')
                ->setColumns(12)
                ->setHelp('Le titre principal qui apparaîtra dans cette section.')
                ->setRequired(false),

            ChoiceField::new('baliseHtml', 'Taille du titre')
                ->setColumns(12)
                ->setChoices([
                    'Grand Titre (H2 - Recommandé)' => 'h2', 
                    'Moyen (H3)'                    => 'h3', 
                    'Petit (H4)'                    => 'h4'
                ])
                ->setRequired(false)->setEmptyData('h2')->hideOnIndex(),
            
            TextareaField::new('contenu', 'Texte & Paragraphes')
                ->setColumns(12)
                ->setNumOfRows(6)
                ->setRequired(false)
                ->setHelp('Rédigez ici le contenu explicatif de cette section.'),

            // ======================================================
            // 2.B BOUTON D'ACTION & LIEN DE REDIRECTION (CTA)
            // ======================================================
            FormField::addFieldset('🔘 Bouton d\'Action & Lien de Redirection (CTA)')->setIcon('fas fa-link'),

            TextField::new('boutonTexte', 'Texte du bouton (ex: Réserver un soin, En savoir plus)')
                ->setColumns(12)
                ->setHelp('Laissez vide si vous ne souhaitez pas afficher de bouton.')
                ->setRequired(false),

            TextField::new('boutonLien', 'Adresse du lien de redirection (URL ou Page)')
                ->setColumns(12)
                ->setHelp('Ex: <code>/reserver/1</code>, <code>/a-propos</code>, ou une adresse complète <code>https://...</code>.')
                ->setRequired(false),

            ChoiceField::new('boutonStyle', 'Style & Couleurs du Bouton')
                ->setColumns(12)
                ->setChoices([
                    '🔱 Doré Premium (Fond: Or #B89A63 | Texte: Noir #0A0A09 | Survol: Or Foncé #9C804F)' => 'gold',
                    '🔲 Contour Épuré (Fond: Transparent | Bordure: Or #B89A63 | Survol: Plein Or #B89A63)' => 'outline',
                    '🌿 Vert Sauge (Fond: Sauge #727763 | Texte: Ivoire #D8D0BE | Survol: Olive #4A4F41)' => 'sage',
                    '🍷 Pourpre Sombre (Fond: Pourpre #2A1A1E | Texte: Or #B89A63 | Survol: Plein Or #B89A63)' => 'plum',
                ])
                ->setHelp('💡 Définit la palette du bouton : couleur de fond, couleur du texte et l\'effet visuel au survol (Hover).')
                ->setRequired(false)->setEmptyData('gold')->hideOnIndex(),

            ChoiceField::new('boutonCible', 'Ouverture du lien')
                ->setColumns(12)
                ->setChoices([
                    'Dans le même onglet (_self)' => '_self',
                    'Dans un nouvel onglet (_blank)' => '_blank',
                ])
                ->setRequired(false)->setEmptyData('_self')->hideOnIndex(),

            // ======================================================
            // 3. IMAGE & ILLUSTRATION (SUPERPOSITION & ROGNAGE)
            // ======================================================
            FormField::addFieldset('🖼️ Image Principale, Rognage & Superposition')->setIcon('fas fa-image'),
            
            Field::new('imageFile', 'Télécharger l\'illustration')
                ->setColumns(12)
                ->setFormType(VichImageType::class)->setRequired(false)->hideOnIndex()
                ->setHelp('💡 <b>Format recommandé :</b> Photo paysage ou portrait (1200x800 px max), format JPG ou WebP, poids idéal < 1 Mo.'),

            TextField::new('imageLien', '🔗 Lien de redirection au clic sur l\'image (URL ou Page)')
                ->setColumns(12)
                ->setHelp('Optionnel : Renseignez une adresse (ex: <code>/reserver/1</code>, <code>/a-propos</code> ou <code>https://...</code>) pour rendre cette photo cliquable.')
                ->setRequired(false),
                
            IntegerField::new('imagePosX', 'Décalage Horizontal Image (%)')
                ->setColumns(12)
                ->setHelp('💡 <code>0</code> = centré. Positif (ex: <code>10</code>) décale à droite, négatif (ex: <code>-10</code>) à gauche.')->hideOnIndex(),
                
            IntegerField::new('imagePosY', 'Décalage Vertical Image (px)')
                ->setColumns(12)
                ->setHelp('💡 <code>0</code> = aligné. Positif (ex: <code>50</code>) vers le bas, négatif (ex: <code>-50</code>) vers le haut.')->hideOnIndex(),

            ChoiceField::new('imageSuperposition', 'Superposition Image & Texte')
                ->setColumns(12)
                ->setChoices([
                    'Standard (A côté)'                      => 'standard',
                    '🖼️ Image PAR-DESSUS le Texte'           => 'image_sur_texte',
                    '📝 Texte PAR-DESSUS l\'Image'           => 'texte_sur_image',
                ])->setRequired(false)->setEmptyData('standard')->hideOnIndex(),

            IntegerField::new('imageZIndex', 'Priorité d\'affichage Z-Index (Empilement)')
                ->setColumns(12)
                ->setHelp('1 = arrière-plan, 2 ou 5 = passe au-dessus des autres éléments.')->hideOnIndex(),

            IntegerField::new('cropHaut', '✂️ Rogner le Haut (%)')
                ->setColumns(12)
                ->setHelp('Pourcentage à supprimer en haut de l\'image (ex: <code>10</code> pour 10%).')->hideOnIndex(),

            IntegerField::new('cropBas', '✂️ Rogner le Bas (%)')
                ->setColumns(12)
                ->setHelp('Pourcentage à supprimer en bas de l\'image (ex: <code>10</code> pour 10%).')->hideOnIndex(),

            IntegerField::new('cropGauche', '✂️ Rogner la Gauche (%)')
                ->setColumns(12)
                ->setHelp('Pourcentage à supprimer à gauche (ex: <code>15</code> pour 15%).')->hideOnIndex(),

            IntegerField::new('cropDroite', '✂️ Rogner la Droite (%)')
                ->setColumns(12)
                ->setHelp('Pourcentage à supprimer à droite (ex: <code>15</code> pour 15%).')->hideOnIndex(),

            IntegerField::new('largeurMedia', 'Largeur sur-mesure (px)')
                ->setColumns(12)
                ->setHelp('Laissez vide pour la taille automatique par défaut.')->setRequired(false)->hideOnIndex(),

            IntegerField::new('hauteurMedia', 'Hauteur sur-mesure (px)')
                ->setColumns(12)
                ->setHelp('Laissez vide pour le ratio automatique.')->setRequired(false)->hideOnIndex(),

            // ======================================================
            // 4. BOÎTE DE CITATION SUPERPOSÉE
            // ======================================================
            FormField::addFieldset('✨ Encart "Citation" ou Phrase d\'Accroche Superposée')->setIcon('fas fa-quote-right'),

            TextareaField::new('citation', 'Texte de la citation')
                ->setColumns(12)
                ->setHelp('Phrase d\'impact mise en valeur dans un encart élégant.')->setRequired(false),

            IntegerField::new('citationHauteurMax', 'Hauteur Max de la citation (px)')
                ->setColumns(12)
                ->setHelp('Ex: <code>300</code> pour faire défiler un long texte, ou laissez vide.')->hideOnIndex(),

            IntegerField::new('citationLargeur', 'Largeur de la boîte (%)')
                ->setColumns(12)
                ->setHelp('Ex: <code>90</code>% (recommandé pour une belle lisibilité).')->hideOnIndex(),

            IntegerField::new('citationPosX', 'Citation - Décalage horizontal (%)')
                ->setColumns(12)
                ->setHelp('Ex: <code>-10</code> (déborde joliment à gauche de la photo), <code>5</code> (décalé à droite).')->hideOnIndex(),
                
            IntegerField::new('citationPosY', 'Citation - Chevauchement vertical (px)')
                ->setColumns(12)
                ->setHelp('💡 <b>Le secret du design :</b> Mettez <code>-150</code> pour faire monter la citation sur la photo, ou <code>0</code> pour un alignement standard.')->hideOnIndex(),

            ChoiceField::new('citationCouleurFond', 'Couleur de Fond de la Citation')
                ->setColumns(12)
                ->setChoices([
                    '🍷 Pourpre Sombre (#2A1A1E - Pourpre officiel)'     => 'meta-plum', 
                    '🌿 Vert Sauge / Olive (#727763 - Fond vert sauge)'  => 'meta-olive',
                    '⬛ Noir Profond (#0A0A09 - Fond sombre)'            => 'meta-black', 
                    '🌿 Vert Sauge (#727763 - Teinte sauge)'              => 'meta-sage', 
                    '🔱 Or Antique (#B89A63 - Fond doré)'                => 'meta-gold', 
                    '📜 Ivoire Délicat (#D8D0BE - Encart clair)'          => 'meta-ivory',
                ])->hideOnIndex(),

            ChoiceField::new('citationCouleurTexte', 'Couleur du Texte de la Citation')
                ->setColumns(12)
                ->setChoices([
                    '📜 Ivoire Délicat (#D8D0BE - Texte clair recommandé)' => 'meta-ivory',
                    '🔱 Or Antique (#B89A63 - Écriture dorée)'            => 'meta-gold',
                    '⬛ Noir Profond (#0A0A09 - Texte sombre)'             => 'meta-black', 
                    '🍷 Pourpre Sombre (#2A1A1E - Écriture pourpre)'       => 'meta-plum', 
                    '🌿 Vert Sauge / Olive (#727763 - Écriture sauge)'     => 'meta-olive',
                    '🌿 Vert Sauge (#727763 - Écriture sauge)'             => 'meta-sage',
                ])->hideOnIndex(),

            // ======================================================
            // 5. MODULES SPÉCIAUX (COLONNES CÔTE À CÔTE & CARROUSEL)
            // ======================================================
            FormField::addFieldset('📊 Colonnes Côte à Côte & Carrousel de Soins')->setIcon('fas fa-columns'),

            CollectionField::new('etapes', '📊 Colonnes Côte à Côte (Pour Grille de Colonnes ou Cheminement)')
                ->setColumns(12)
                ->setHelp('💡 <b>Pour ajouter vos colonnes (ex: Colonne 1 & Colonne 2) :</b> Cliquez sur "Ajouter un nouvel élément" ci-dessous pour créer chaque colonne de votre tableau comparatif.')
                ->useEntryCrudForm(EtapeCrudController::class)->hideOnIndex(),

            AssociationField::new('prestations', 'Prestations à afficher dans le Carrousel')
                ->setColumns(12)
                ->setHelp('Sélectionnez les prestations à mettre en valeur si vous avez choisi la disposition "Carrousel des Prestations".')
                ->setFormTypeOptions(['by_reference' => false])->hideOnIndex(),
        ];
    }
}