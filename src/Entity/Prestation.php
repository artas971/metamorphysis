<?php

namespace App\Entity;

use App\Repository\PrestationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PrestationRepository::class)]
#[Vich\Uploadable]
class Prestation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    // Prix de base unitaire (par personne et par séance)
    #[ORM\Column]
    private ?float $prix = null;

    // Texte commercial libre affiché sur les cartes et le site (ex: "À partir de 80 €", "Entre 80 € et 120 €", "Sur devis")
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prixAffiche = null;

    // Nombre de personnes minimum (ex: 1 pour individuel, 2 pour couple)
    #[ORM\Column(options: ['default' => 1])]
    private int $minPersonnes = 1;

    // Nombre de personnes maximum (ex: 1, 2, 6, 10...)
    #[ORM\Column(options: ['default' => 1])]
    private int $maxPersonnes = 1;

    #[ORM\Column(nullable: true)]
    private ?int $duree = null;
    
    // --- CHAMP ICÔNE ---
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icone = null;

    // --- NOUVEAU CHAMP UNITÉ DE PRIX ---
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $unitePrix = null;

    #[ORM\Column(type: 'boolean')]
    private bool $estMisEnAvant = false;

    #[ORM\Column(nullable: true)]
    private ?int $ordre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionComplementaire = null;

    // --- LIEN VIDÉO (Optionnel) ---
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lienVideo = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'prestation', orphanRemoval: true)]
    private Collection $Entrée;

    // Ce champ ne va pas dans la base de données, il sert juste à manipuler le fichier uploadé
    #[Vich\UploadableField(mapping: 'prestations_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    // Ce champ va dans la base de données pour stocker le nom (ex: mon-image-64a2b.jpg)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    // VichUploader a besoin de savoir quand l'image a été modifiée pour forcer la mise à jour
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?int $nombreSeances = 1;

    /**
     * @var Collection<int, Seance>
     */
    #[ORM\OneToMany(targetEntity: Seance::class, mappedBy: 'prestation')]
    private Collection $seances;

    public function __construct()
    {
        $this->Entrée = new ArrayCollection();
        $this->seances = new ArrayCollection();
        $this->minPersonnes = 1;
        $this->maxPersonnes = 1;
        $this->nombreSeances = 1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getPrixAffiche(): ?string
    {
        return $this->prixAffiche;
    }

    public function setPrixAffiche(?string $prixAffiche): static
    {
        $this->prixAffiche = $prixAffiche;

        return $this;
    }

    public function getMinPersonnes(): int
    {
        return $this->minPersonnes ?? 1;
    }

    public function setMinPersonnes(?int $minPersonnes): static
    {
        $this->minPersonnes = $minPersonnes ?? 1;

        return $this;
    }

    public function getMaxPersonnes(): int
    {
        return $this->maxPersonnes ?? 1;
    }

    public function setMaxPersonnes(?int $maxPersonnes): static
    {
        $this->maxPersonnes = $maxPersonnes ?? 1;

        return $this;
    }

    public function hasChoixPersonnes(): bool
    {
        return $this->getMaxPersonnes() > $this->getMinPersonnes();
    }

    public function hasTarificationVariable(): bool
    {
        return $this->hasChoixPersonnes();
    }

    /**
     * Calcule le prix total exact : Nombre de personnes x Nombre de séances x Prix de base
     */
    public function calculerPrixTotal(?int $nombrePersonnes = null): float
    {
        $personnes = $nombrePersonnes ?? $this->getMinPersonnes();
        if ($personnes < $this->getMinPersonnes()) {
            $personnes = $this->getMinPersonnes();
        }
        if ($personnes > $this->getMaxPersonnes() && $this->getMaxPersonnes() >= $this->getMinPersonnes()) {
            $personnes = $this->getMaxPersonnes();
        }

        $seances = $this->getNombreSeances() ?? 1;
        $prixBase = (float) ($this->prix ?? 0.0);

        return round($personnes * $seances * $prixBase, 2);
    }

    public function getLibelleFormule(?int $nombrePersonnes): string
    {
        $personnes = $nombrePersonnes ?? $this->getMinPersonnes();
        if ($personnes === 1) {
            return '1 personne';
        }
        if ($personnes === 2) {
            return 'Formule Couple (2 personnes)';
        }
        return 'Formule Groupe (' . $personnes . ' personnes)';
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getIcone(): ?string
    {
        return $this->icone;
    }

    public function setIcone(?string $icone): static
    {
        $this->icone = $icone;

        return $this;
    }

    public function getUnitePrix(): ?string
    {
        return $this->unitePrix;
    }

    public function setUnitePrix(?string $unitePrix): static
    {
        $this->unitePrix = $unitePrix;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getEntrée(): Collection
    {
        return $this->Entrée;
    }

    public function addEntrE(Reservation $entrE): static
    {
        if (!$this->Entrée->contains($entrE)) {
            $this->Entrée->add($entrE);
            $entrE->setPrestation($this);
        }

        return $this;
    }

    public function removeEntrE(Reservation $entrE): static
    {
        if ($this->Entrée->removeElement($entrE)) {
            if ($entrE->getPrestation() === $this) {
                $entrE->setPrestation(null);
            }
        }

        return $this;
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

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;
        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
 
    public function isEstMisEnAvant(): bool
    {
        return $this->estMisEnAvant;
    }

    public function setEstMisEnAvant(bool $estMisEnAvant): self
    {
        $this->estMisEnAvant = $estMisEnAvant;
        return $this;
    }
    
    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }
    
    public function getDescriptionComplementaire(): ?string
    {
        return $this->descriptionComplementaire;
    }

    public function setDescriptionComplementaire(?string $descriptionComplementaire): static
    {
        $this->descriptionComplementaire = $descriptionComplementaire;
        return $this;
    }

    public function getLienVideo(): ?string
    {
        return $this->lienVideo;
    }

    public function setLienVideo(?string $lienVideo): static
    {
        $this->lienVideo = $lienVideo;
        return $this;
    }

    public function getNombreSeances(): ?int
    {
        return $this->nombreSeances ?? 1;
    }

    public function setNombreSeances(int $nombreSeances): static
    {
        $this->nombreSeances = $nombreSeances;

        return $this;
    }

    /**
     * @return Collection<int, Seance>
     */
    public function getSeances(): Collection
    {
        return $this->seances;
    }

    public function addSeance(Seance $seance): static
    {
        if (!$this->seances->contains($seance)) {
            $this->seances->add($seance);
            $seance->setPrestation($this);
        }

        return $this;
    }

    public function removeSeance(Seance $seance): static
    {
        if ($this->seances->removeElement($seance)) {
            if ($seance->getPrestation() === $this) {
                $seance->setPrestation(null);
            }
        }

        return $this;
    }
}