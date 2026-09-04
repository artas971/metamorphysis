<?php

namespace App\Entity;

use App\Repository\PrestationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\AsciiSlugger;
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

    // Slug lisible pour les URLs (ex: /prestation/couple-et-relations)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    // Prix de base / 1ère formule (gardé pour cohérence DB et listing par défaut)
    #[ORM\Column]
    private ?float $prix = null;

    // Tarif fixé pour 2 personnes (compatibilité)
    #[ORM\Column(nullable: true)]
    private ?float $prixCouple = null;

    // Tarif fixé pour 3+ personnes (compatibilité)
    #[ORM\Column(nullable: true)]
    private ?float $prixGroupe = null;

    // Texte commercial libre affiché sur les cartes et le site (ex: "À partir de 80 €", "Entre 80 € et 120 €", "Sur devis")
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prixAffiche = null;

    // Nombre de personnes minimum (ex: 1, 2...)
    #[ORM\Column(options: ['default' => 1])]
    private int $minPersonnes = 1;

    // Nombre de personnes maximum (ex: 1, 3, 5...)
    #[ORM\Column(options: ['default' => 1])]
    private int $maxPersonnes = 1;

    // Grille tarifaire dynamique JSON stockant le prix exact, titre et sous-titre pour chaque palier
    // Ex: {"2": {"prix": 80, "titre": "2 personnes", "sousTitre": "Meilleur ami"}, "3": {"prix": 120, "titre": "3 personnes et +", "sousTitre": "Trouple"}}
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $tarifsParPersonne = [];

    #[ORM\Column(nullable: true)]
    private ?int $duree = null;
    
    // --- CHAMP ICÔNE ---
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icone = null;

    // --- CHAMP UNITÉ DE PRIX ---
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

    // Manipulation du fichier uploadé
    #[Vich\UploadableField(mapping: 'prestations_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    // Stockage du nom d'image en base
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?int $nombreSeances = 1;

    /**
     * @var Collection<int, Seance>
     */
    #[ORM\OneToMany(targetEntity: Seance::class, mappedBy: 'prestation')]
    private Collection $seances;

    // --- CHAMPS THÉRAPIE / ATELIER DE GROUPE ---
    #[ORM\Column(type: 'boolean')]
    private bool $estCollectif = false;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $labelCollectif = 'ATELIER COLLECTIF';

    #[ORM\Column]
    private ?int $seuilMinimum = 5;

    #[ORM\Column]
    private ?int $capaciteMaximale = 10;

    #[ORM\Column]
    private ?int $delaiLimiteHeures = 24;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $recurrence = null;

    /**
     * @var Collection<int, SessionGroupe>
     */
    #[ORM\OneToMany(targetEntity: SessionGroupe::class, mappedBy: 'prestation', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $sessionsGroupe;

    public function __construct()
    {
        $this->Entrée = new ArrayCollection();
        $this->seances = new ArrayCollection();
        $this->sessionsGroupe = new ArrayCollection();
        $this->minPersonnes = 1;
        $this->maxPersonnes = 1;
        $this->nombreSeances = 1;
        $this->tarifsParPersonne = [];
        $this->estCollectif = false;
        $this->seuilMinimum = 5;
        $this->capaciteMaximale = 10;
        $this->delaiLimiteHeures = 24;
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
        if (empty($this->slug)) {
            $this->slug = (new AsciiSlugger())->slug($nom)->lower()->toString();
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        if (empty($this->slug) && !empty($this->nom)) {
            return (new AsciiSlugger())->slug($this->nom)->lower()->toString();
        }
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

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

    public function getPrixCouple(): ?float
    {
        return $this->prixCouple;
    }

    public function setPrixCouple(?float $prixCouple): static
    {
        $this->prixCouple = $prixCouple;

        return $this;
    }

    public function getPrixGroupe(): ?float
    {
        return $this->prixGroupe;
    }

    public function setPrixGroupe(?float $prixGroupe): static
    {
        $this->prixGroupe = $prixGroupe;

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

    public function setMinPersonnes(?int $minPersonnes): static
    {
        $this->minPersonnes = max(1, $minPersonnes ?? 1);

        return $this;
    }

    public function getMinPersonnes(): int
    {
        return max(1, $this->minPersonnes ?? 1);
    }

    public function getMaxPersonnes(): int
    {
        $min = $this->getMinPersonnes();
        $max = $this->maxPersonnes ?? $min;

        return max($min, $max);
    }

    public function setMaxPersonnes(?int $maxPersonnes): static
    {
        $this->maxPersonnes = $maxPersonnes ?? 1;

        return $this;
    }

    public function getNombrePrix(): int
    {
        $tarifs = $this->getTarifsParPersonne();
        if (!empty($tarifs)) {
            return count($tarifs);
        }
        return max(1, $this->getMaxPersonnes() - $this->getMinPersonnes() + 1);
    }

    public function setNombrePrix(?int $nombrePrix): static
    {
        $nb = max(1, $nombrePrix ?? 1);
        $this->minPersonnes = 1;
        $this->maxPersonnes = $nb;

        return $this;
    }

    public function getTarifsParPersonne(): array
    {
        return $this->tarifsParPersonne ?? [];
    }

    public function setTarifsParPersonne(?array $tarifsParPersonne): static
    {
        $this->tarifsParPersonne = $tarifsParPersonne ?? [];

        return $this;
    }

    public function getTarifsParPersonneJson(): string
    {
        return json_encode($this->getTarifsParPersonne(), JSON_UNESCAPED_UNICODE);
    }

    public function setTarifsParPersonneJson(?string $json): static
    {
        if ($json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $this->tarifsParPersonne = $decoded;
            }
        }
        return $this;
    }

    public function hasTarificationVariable(): bool
    {
        return $this->getMaxPersonnes() > $this->getMinPersonnes();
    }

    public function hasChoixPersonnes(): bool
    {
        return $this->hasTarificationVariable();
    }

    public function isConsultationInitiale(): bool
    {
        $slug = $this->getSlug();
        if ($slug && str_contains($slug, 'consultation-initiale')) {
            return true;
        }

        $nom = mb_strtolower($this->nom ?? '');
        return str_contains($nom, 'consultation initiale') || str_contains($nom, 'bilan initial');
    }

    /**
     * Retourne les détails complets (prix, titre, sous-titre) pour un palier donné
     */
    public function getFormuleDetails(?int $nombrePersonnes = null): array
    {
        $min = $this->getMinPersonnes();
        $max = $this->getMaxPersonnes();

        if ($nombrePersonnes === null || $nombrePersonnes < $min) {
            $nombrePersonnes = $min;
        } elseif ($nombrePersonnes > $max) {
            $nombrePersonnes = $max;
        }

        $tarifs = $this->getTarifsParPersonne();
        $key = (string) $nombrePersonnes;

        $defaultTitre = ($nombrePersonnes === $max && $nombrePersonnes > 2) 
            ? $nombrePersonnes . ' personnes et +' 
            : $nombrePersonnes . ($nombrePersonnes > 1 ? ' personnes' : ' personne');

        $defaultSousTitre = match ($nombrePersonnes) {
            1 => 'Individuel',
            2 => 'Couple / Duo',
            default => 'Groupe / Famille'
        };

        if (isset($tarifs[$key])) {
            $item = $tarifs[$key];
            if (is_array($item)) {
                return [
                    'prix' => isset($item['prix']) && is_numeric($item['prix']) ? (float) $item['prix'] : (float) ($this->prix ?? 0),
                    'titre' => !empty($item['titre']) ? $item['titre'] : $defaultTitre,
                    'sousTitre' => isset($item['sousTitre']) ? $item['sousTitre'] : $defaultSousTitre,
                ];
            } elseif (is_numeric($item)) {
                return [
                    'prix' => (float) $item,
                    'titre' => $defaultTitre,
                    'sousTitre' => $defaultSousTitre,
                ];
            }
        } else {
            $values = array_values($tarifs);
            $idx = $nombrePersonnes - $min;
            if (isset($values[$idx])) {
                $item = $values[$idx];
                if (is_array($item)) {
                    return [
                        'prix' => isset($item['prix']) && is_numeric($item['prix']) ? (float) $item['prix'] : (float) ($this->prix ?? 0),
                        'titre' => !empty($item['titre']) ? $item['titre'] : $defaultTitre,
                        'sousTitre' => isset($item['sousTitre']) ? $item['sousTitre'] : $defaultSousTitre,
                    ];
                } elseif (is_numeric($item)) {
                    return [
                        'prix' => (float) $item,
                        'titre' => $defaultTitre,
                        'sousTitre' => $defaultSousTitre,
                    ];
                }
            }
        }

        // Fallbacks
        $prix = (float) ($this->prix ?? 0);
        if ($nombrePersonnes === 2 && $this->prixCouple !== null) {
            $prix = (float) $this->prixCouple;
        } elseif ($nombrePersonnes >= 3 && $this->prixGroupe !== null) {
            $prix = (float) $this->prixGroupe;
        }

        return [
            'prix' => $prix,
            'titre' => $defaultTitre,
            'sousTitre' => $defaultSousTitre,
        ];
    }

    /**
     * Retourne le tarif exact pour le nombre de personnes sélectionné
     */
    public function getTarifPour(?int $nombrePersonnes = null): float
    {
        return $this->getFormuleDetails($nombrePersonnes)['prix'];
    }

    /**
     * Calcule le montant à régler selon la formule choisie
     */
    public function calculerPrix(?int $nombrePersonnes = null): float
    {
        $nb = $nombrePersonnes ?? $this->getMinPersonnes();
        return $this->getTarifPour($nb);
    }

    public function calculerPrixTotal(?int $nombrePersonnes = null): float
    {
        return $this->calculerPrix($nombrePersonnes);
    }

    public function getLibelleFormule(?int $nombrePersonnes = null): string
    {
        $details = $this->getFormuleDetails($nombrePersonnes);
        $titre = !empty($details['titre']) ? $details['titre'] : ($this->getMinPersonnes() . ' personnes');
        $sousTitre = !empty($details['sousTitre']) ? ' (' . $details['sousTitre'] . ')' : '';
        return $titre . $sousTitre;
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

    public function isEstCollectif(): bool
    {
        return $this->estCollectif;
    }

    public function setEstCollectif(bool $estCollectif): static
    {
        $this->estCollectif = $estCollectif;
        return $this;
    }

    public function getLabelCollectif(): ?string
    {
        return $this->labelCollectif ?: 'ATELIER COLLECTIF';
    }

    public function setLabelCollectif(?string $labelCollectif): static
    {
        $this->labelCollectif = $labelCollectif ? trim($labelCollectif) : 'ATELIER COLLECTIF';
        return $this;
    }

    public function getSeuilMinimum(): int
    {
        return $this->seuilMinimum ?? 5;
    }

    public function setSeuilMinimum(int $seuilMinimum): static
    {
        $this->seuilMinimum = $seuilMinimum;
        return $this;
    }

    public function getCapaciteMaximale(): int
    {
        return $this->capaciteMaximale ?? 10;
    }

    public function setCapaciteMaximale(int $capaciteMaximale): static
    {
        $this->capaciteMaximale = $capaciteMaximale;
        return $this;
    }

    public function getDelaiLimiteHeures(): int
    {
        return $this->delaiLimiteHeures ?? 24;
    }

    public function setDelaiLimiteHeures(int $delaiLimiteHeures): static
    {
        $this->delaiLimiteHeures = $delaiLimiteHeures;
        return $this;
    }

    public function getRecurrence(): ?string
    {
        return $this->recurrence;
    }

    public function setRecurrence(?string $recurrence): static
    {
        $this->recurrence = $recurrence;
        return $this;
    }

    /**
     * @return Collection<int, SessionGroupe>
     */
    public function getSessionsGroupe(): Collection
    {
        return $this->sessionsGroupe;
    }

    public function addSessionGroupe(SessionGroupe $sessionGroupe): static
    {
        if (!$this->sessionsGroupe->contains($sessionGroupe)) {
            $this->sessionsGroupe->add($sessionGroupe);
            $sessionGroupe->setPrestation($this);
        }
        return $this;
    }

    public function removeSessionGroupe(SessionGroupe $sessionGroupe): static
    {
        if ($this->sessionsGroupe->removeElement($sessionGroupe)) {
            if ($sessionGroupe->getPrestation() === $this) {
                $sessionGroupe->setPrestation(null);
            }
        }
        return $this;
    }
}