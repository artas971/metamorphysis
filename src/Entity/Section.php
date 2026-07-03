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
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $baliseHtml = 'section';
    
    // La bonne propriété $etapes, unique et bien configurée
    #[ORM\OneToMany(mappedBy: 'section', targetEntity: Etape::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $etapes;

    #[Vich\UploadableField(mapping: 'pages_images', fileNameProperty: 'media')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function setDisposition(string $disposition): static
    {
        $this->disposition = $disposition;

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
            'texte_centre' => '📝 Texte centré',
            'img_gauche' => '🖼️ Image à Gauche + Texte',
            'img_droite' => '🖼️ Texte + Image à Droite',
            'banniere' => '✨ Bannière pleine largeur',
            'slider_prestations' => '🎠 Slider des Prestations',
            'info_pratique' => '🌸 Bloc Info Pratique',
        ];

        $label = $dispositionLabels[$this->disposition] ?? 'Nouveau bloc';
        
        return sprintf('%s (Position %d)', $label, $this->ordre);
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
}