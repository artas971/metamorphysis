<?php

namespace App\Entity;

use App\Repository\SectionRepository;
use Doctrine\DBAL\Types\Types;
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


    public function __construct()
    {
        $this->disposition = 'texte_centre';
        $this->ordre = 0;
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
        // Cela affichera "Bloc - texte_centre" ou "Bloc - img_gauche" par exemple
        return 'Bloc de type : ' . ($this->disposition ?? 'Nouveau');
    }
    // 3. Ajoute le champ virtuel pour le fichier
    #[Vich\UploadableField(mapping: 'pages_images', fileNameProperty: 'media')]
    private ?File $imageFile = null;

    // 4. Ajoute un champ date de mise à jour (obligatoire pour que Vich fonctionne bien)
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    // 5. Ajoute les Getters et Setters tout en bas de la classe
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
}
