<?php

namespace App\Entity;

use App\Repository\GroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupeRepository::class)]
class Groupe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Prestation $prestation = null;

    /**
     * Membres réguliers faisant partie de la cohorte
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'groupe_membres')]
    private Collection $membres;

    /**
     * Sessions ordonnées de cette cohorte (Séance 1, Séance 2, ...)
     * @var Collection<int, SessionGroupe>
     */
    #[ORM\OneToMany(targetEntity: SessionGroupe::class, mappedBy: 'groupe', cascade: ['persist'])]
    #[ORM\OrderBy(['numeroSeance' => 'ASC', 'dateDebut' => 'ASC'])]
    private Collection $sessions;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateCreation = null;

    // Statut du groupe : 'Actif', 'Clôturé'
    #[ORM\Column(length: 50)]
    private ?string $statut = 'Actif';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->membres = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->statut = 'Actif';
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

    public function getPrestation(): ?Prestation
    {
        return $this->prestation;
    }

    public function setPrestation(?Prestation $prestation): static
    {
        $this->prestation = $prestation;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembres(): Collection
    {
        return $this->membres;
    }

    public function addMembre(User $membre): static
    {
        if (!$this->membres->contains($membre)) {
            $this->membres->add($membre);
        }
        return $this;
    }

    public function removeMembre(User $membre): static
    {
        $this->membres->removeElement($membre);
        return $this;
    }

    /**
     * @return Collection<int, SessionGroupe>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(SessionGroupe $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setGroupe($this);
        }
        return $this;
    }

    public function removeSession(SessionGroupe $session): static
    {
        if ($this->sessions->removeElement($session)) {
            if ($session->getGroupe() === $this) {
                $session->setGroupe(null);
            }
        }
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    // --- HELPERS MÉTIER ---

    public function getNombreSessions(): int
    {
        return $this->sessions->count();
    }

    public function getDerniereSession(): ?SessionGroupe
    {
        if ($this->sessions->isEmpty()) {
            return null;
        }

        $sessionsArray = $this->sessions->toArray();
        usort($sessionsArray, function (SessionGroupe $a, SessionGroupe $b) {
            return ($b->getNumeroSeance() ?? 0) <=> ($a->getNumeroSeance() ?? 0);
        });

        return $sessionsArray[0];
    }

    public function getProchaineSession(): ?SessionGroupe
    {
        $now = new \DateTime();
        foreach ($this->sessions as $session) {
            if ($session->getDateDebut() >= $now && $session->getStatut() !== 'Annulé') {
                return $session;
            }
        }
        return null;
    }

    public function __toString(): string
    {
        $prestaNom = $this->prestation ? $this->prestation->getNom() : 'Accompagnement';
        return sprintf('%s (%s)', $this->nom, $prestaNom);
    }
}
