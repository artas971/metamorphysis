<?php

namespace App\Entity;

use App\Repository\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Attribute as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: SectionRepository::class)]
#[Vich\Uploadable]
class Section
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Page $page = null;

    #[ORM\Column]
    private ?int $ordre = 0;  
    
    #[ORM\Column(length: 255)]
    private ?string $disposition = 'texte_centre'; 

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $media = null;

    #[ORM\Column(nullable: true)]
    private ?int $largeurMedia = null;

    #[ORM\Column(nullable: true)]
    private ?int $hauteurMedia = null;
 
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(length: 50, nullable: true, options: ['default' => 'gold-hover'])]
    private ?string $titreCouleur = 'gold-hover';

    #[ORM\Column(length: 50, nullable: true, options: ['default' => 'apres'])]
    private ?string $titreLigneDecor = 'apres';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $sousTitre = null;

    #[ORM\Column(length: 50, nullable: true, options: ['default' => 'ivory'])]
    private ?string $sousTitreCouleur = 'ivory';

    #[ORM\Column(length: 50, nullable: true, options: ['default' => 'ivory'])]
    private ?string $texteCouleur = 'ivory';
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $baliseHtml = 'section';

    #[ORM\Column(length: 50, nullable: true, options: ['default' => 'plum'])]
    private ?string $couleurFond = 'plum';
    
    #[ORM\OneToMany(mappedBy: 'section', targetEntity: Etape::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $etapes;

    #[Vich\UploadableField(mapping: 'pages_images', fileNameProperty: 'media')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $citation = null;
    
    #[ORM\Column(nullable: true)]
    private ?int $citationPosX = -10; // Décalage horizontal (en %)

    #[ORM\Column(nullable: true)]
    private ?int $citationPosY = -40; // Décalage vertical (en px)

    #[ORM\Column(nullable: true)]
    private ?int $citationLargeur = 90; // Largeur de la boîte (en %)

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $citationCouleurFond = 'meta-olive'; // Couleur de fond

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $citationCouleurTexte = 'meta-gold'; // Couleur du text
    
    #[ORM\Column(nullable: true)]
    private ?int $imagePosX = 0; // Décalage horizontal de l'image

    #[ORM\Column(nullable: true)]
    private ?int $imagePosY = 0; // Décalage vertical de l'image

    #[ORM\Column(nullable: true)]
    private ?int $citationHauteurMax = null;

    // --- BOUTON DE REDIRECTION (CTA) ---
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $boutonTexte = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $boutonLien = null;

    #[ORM\Column(length: 50, nullable: true, options: ['default' => 'gold'])]
    private ?string $boutonStyle = 'gold';

    #[ORM\Column(length: 20, nullable: true, options: ['default' => '_self'])]
    private ?string $boutonCible = '_self';

    // --- SUPERPOSITION IMAGE / TEXTE ---
    #[ORM\Column(length: 50, nullable: true, options: ['default' => 'standard'])]
    private ?string $imageSuperposition = 'standard';

    #[ORM\Column(nullable: true, options: ['default' => 1])]
    private ?int $imageZIndex = 1;

    // --- ROGNAGE IMAGE (CROP %) ---
    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?int $cropHaut = 0;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?int $cropBas = 0;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?int $cropGauche = 0;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?int $cropDroite = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageLien = null;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?int $decalagePosY = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private ?bool $imageCadre = true;

    #[ORM\Column(length: 50, nullable: true, options: ['default' => 'plum'])]
    private ?string $imageCadreCouleur = 'plum';

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?int $imageCadreHaut = 0;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?int $imageCadreBas = 0;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?int $imageCadreGauche = 0;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?int $imageCadreDroite = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $texteGras = false;

    /**
     * @var Collection<int, Prestation>
     */
    #[ORM\ManyToMany(targetEntity: Prestation::class)]
    private Collection $prestations;

    public function __construct()
    {
        $this->disposition = 'texte_centre';
        $this->ordre = 0;
        $this->prestations = new ArrayCollection();
        $this->etapes = new ArrayCollection();
    }

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getDisposition(): ?string
    {
        return $this->disposition;
    }

    public function setDisposition(?string $disposition): static
        {
            // Le "?string" autorise le null.
            // Si EasyAdmin envoie null, on sécurise en forçant le design par défaut
            $this->disposition = $disposition ?? 'texte_centre';

            return $this;
        }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedia(?string $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getLargeurMedia(): ?int
    {
        return $this->largeurMedia;
    }

    public function setLargeurMedia(?int $largeurMedia): static
    {
        $this->largeurMedia = $largeurMedia;

        return $this;
    }

    public function getHauteurMedia(): ?int
    {
        return $this->hauteurMedia;
    }

    public function setHauteurMedia(?int $hauteurMedia): static
    {
        $this->hauteurMedia = $hauteurMedia;

        return $this;
    }
    
    public function __toString(): string
    {
        $dispositionLabels = [
            'texte_centre'       => '📄 Texte Centré',
            'img_gauche'         => '🖼️ Image à Gauche + Texte',
            'img_droite'         => '🖼️ Texte à Gauche + Image',
            'img_centre'         => '🖼️ Image au Centre + Textes',
            'grille_colonnes'    => '📊 Grille Multi-Colonnes (2 à 5 colonnes)',
            'banniere'           => '🌅 Bannière Pleine Largeur',
            'slider_prestations' => '🎠 Carrousel des Prestations',
            'bandeau_conclusion' => '🌸 Bandeau Signature & Logo M',
            'info_pratique'      => '🌸 Bloc Info Pratique',
        ];

        $label = $dispositionLabels[$this->disposition] ?? 'Bloc';
        $titre = !empty($this->titre) ? ' : ' . $this->titre : '';
        
        return sprintf('%s (Position %d)%s', $label, $this->ordre ?? 0, $titre);
    }
    
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getTitreCouleur(): ?string
    {
        return $this->titreCouleur ?? 'gold';
    }

    public function setTitreCouleur(?string $titreCouleur): static
    {
        $this->titreCouleur = $titreCouleur;

        return $this;
    }

    public function getSousTitre(): ?string
    {
        return $this->sousTitre;
    }

    public function setSousTitre(?string $sousTitre): static
    {
        $this->sousTitre = $sousTitre;

        return $this;
    }

    public function getSousTitreCouleur(): ?string
    {
        return $this->sousTitreCouleur ?? 'ivory';
    }

    public function setSousTitreCouleur(?string $sousTitreCouleur): static
    {
        $this->sousTitreCouleur = $sousTitreCouleur;

        return $this;
    }

    public function getTexteCouleur(): ?string
    {
        return $this->texteCouleur ?? 'ivory';
    }

    public function setTexteCouleur(?string $texteCouleur): static
    {
        $this->texteCouleur = $texteCouleur;

        return $this;
    }

    public function getBaliseHtml(): ?string
    {
        return $this->baliseHtml;
    }

    public function setBaliseHtml(?string $baliseHtml): static
    {
        $this->baliseHtml = $baliseHtml;

        return $this;
    }

    /**
     * @return Collection<int, Prestation>
     */
    public function getPrestations(): Collection
    {
        return $this->prestations;
    }

    public function addPrestation(Prestation $prestation): static
    {
        if (!$this->prestations->contains($prestation)) {
            $this->prestations->add($prestation);
        }

        return $this;
    }

    public function removePrestation(Prestation $prestation): static
    {
        $this->prestations->removeElement($prestation);

        return $this;
    }

    /**
     * @return Collection<int, Etape>
     */
    public function getEtapes(): Collection
    {
        return $this->etapes;
    }

    public function addEtape(Etape $etape): static
    {
        if (!$this->etapes->contains($etape)) {
            $this->etapes->add($etape);
            $etape->setSection($this);
        }

        return $this;
    }

    public function removeEtape(Etape $etape): static
    {
        if ($this->etapes->removeElement($etape)) {
            // set the owning side to null (unless already changed)
            if ($etape->getSection() === $this) {
                $etape->setSection(null);
            }
        }

        return $this;
    }

    public function getCouleurFond(): ?string
    {
        return $this->couleurFond;
    }

    public function setCouleurFond(?string $couleurFond): static
    {
        $this->couleurFond = $couleurFond;

        return $this;
    }

    // --- NOUVEAUX GETTER / SETTER POUR LA CITATION ---
    public function getCitation(): ?string
    {
        return $this->citation;
    }

    public function setCitation(?string $citation): static
    {
        $this->citation = $citation;

        return $this;
    }

    public function getCitationPosX(): ?int { return $this->citationPosX; }
    public function setCitationPosX(?int $citationPosX): static { $this->citationPosX = $citationPosX; return $this; }

    public function getCitationPosY(): ?int { return $this->citationPosY; }
    public function setCitationPosY(?int $citationPosY): static { $this->citationPosY = $citationPosY; return $this; }

    public function getCitationLargeur(): ?int { return $this->citationLargeur; }
    public function setCitationLargeur(?int $citationLargeur): static { $this->citationLargeur = $citationLargeur; return $this; }

    public function getCitationCouleurFond(): ?string { return $this->citationCouleurFond; }
    public function setCitationCouleurFond(?string $citationCouleurFond): static { $this->citationCouleurFond = $citationCouleurFond; return $this; }

    public function getCitationCouleurTexte(): ?string { return $this->citationCouleurTexte; }
    public function setCitationCouleurTexte(?string $citationCouleurTexte): static { $this->citationCouleurTexte = $citationCouleurTexte; return $this; }
    
    public function getImagePosX(): ?int { return $this->imagePosX; }
    public function setImagePosX(?int $imagePosX): static { $this->imagePosX = $imagePosX; return $this; }

    public function getImagePosY(): ?int { return $this->imagePosY; }
    public function setImagePosY(?int $imagePosY): static { $this->imagePosY = $imagePosY; return $this; }

    public function getCitationHauteurMax(): ?int { return $this->citationHauteurMax; }
    public function setCitationHauteurMax(?int $citationHauteurMax): static { $this->citationHauteurMax = $citationHauteurMax; return $this; }

    // --- GETTERS / SETTERS BOUTON CTA ---
    public function getBoutonTexte(): ?string { return $this->boutonTexte; }
    public function setBoutonTexte(?string $boutonTexte): static { $this->boutonTexte = $boutonTexte; return $this; }

    public function getBoutonLien(): ?string { return $this->boutonLien; }
    public function setBoutonLien(?string $boutonLien): static { $this->boutonLien = $boutonLien; return $this; }

    public function getBoutonStyle(): ?string { return $this->boutonStyle; }
    public function setBoutonStyle(?string $boutonStyle): static { $this->boutonStyle = $boutonStyle; return $this; }

    public function getBoutonCible(): ?string { return $this->boutonCible; }
    public function setBoutonCible(?string $boutonCible): static { $this->boutonCible = $boutonCible; return $this; }

    // --- GETTERS / SETTERS SUPERPOSITION ---
    public function getImageSuperposition(): ?string { return $this->imageSuperposition; }
    public function setImageSuperposition(?string $imageSuperposition): static { $this->imageSuperposition = $imageSuperposition; return $this; }

    public function getImageZIndex(): ?int { return $this->imageZIndex; }
    public function setImageZIndex(?int $imageZIndex): static { $this->imageZIndex = $imageZIndex; return $this; }

    // --- GETTERS / SETTERS ROGNAGE ---
    public function getCropHaut(): ?int { return $this->cropHaut; }
    public function setCropHaut(?int $cropHaut): static { $this->cropHaut = $cropHaut; return $this; }

    public function getCropBas(): ?int { return $this->cropBas; }
    public function setCropBas(?int $cropBas): static { $this->cropBas = $cropBas; return $this; }

    public function getCropGauche(): ?int { return $this->cropGauche; }
    public function setCropGauche(?int $cropGauche): static { $this->cropGauche = $cropGauche; return $this; }

    public function getCropDroite(): ?int { return $this->cropDroite; }
    public function setCropDroite(?int $cropDroite): static { $this->cropDroite = $cropDroite; return $this; }

    public function getImageLien(): ?string { return $this->imageLien; }
    public function setImageLien(?string $imageLien): static { $this->imageLien = $imageLien; return $this; }

    public function getDecalagePosY(): ?int { return $this->decalagePosY; }
    public function setDecalagePosY(?int $decalagePosY): static { $this->decalagePosY = $decalagePosY; return $this; }

    public function isImageCadre(): ?bool { return $this->imageCadre ?? true; }
    public function setImageCadre(?bool $imageCadre): static { $this->imageCadre = $imageCadre; return $this; }

    public function getImageCadreCouleur(): ?string { return $this->imageCadreCouleur ?? 'plum'; }
    public function setImageCadreCouleur(?string $imageCadreCouleur): static { $this->imageCadreCouleur = $imageCadreCouleur; return $this; }

    public function getImageCadreHaut(): ?int { return $this->imageCadreHaut ?? 0; }
    public function setImageCadreHaut(?int $imageCadreHaut): static { $this->imageCadreHaut = $imageCadreHaut; return $this; }

    public function getImageCadreBas(): ?int { return $this->imageCadreBas ?? 0; }
    public function setImageCadreBas(?int $imageCadreBas): static { $this->imageCadreBas = $imageCadreBas; return $this; }

    public function getImageCadreGauche(): ?int { return $this->imageCadreGauche ?? 0; }
    public function setImageCadreGauche(?int $imageCadreGauche): static { $this->imageCadreGauche = $imageCadreGauche; return $this; }

    public function getImageCadreDroite(): ?int { return $this->imageCadreDroite ?? 0; }
    public function setImageCadreDroite(?int $imageCadreDroite): static { $this->imageCadreDroite = $imageCadreDroite; return $this; }

    public function isTexteGras(): ?bool { return $this->texteGras ?? false; }
    public function setTexteGras(?bool $texteGras): static { $this->texteGras = $texteGras; return $this; }

    public function getTitreLigneDecor(): ?string { return $this->titreLigneDecor ?? 'apres'; }
    public function setTitreLigneDecor(?string $titreLigneDecor): static { $this->titreLigneDecor = $titreLigneDecor ?? 'apres'; return $this; }
}