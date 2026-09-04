<?php

namespace App\Entity;

use App\Repository\SessionGroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionGroupeRepository::class)]
class SessionGroupe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sessionsGroupe')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Prestation $prestation = null;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Groupe $groupe = null;

    #[ORM\Column(nullable: true)]
    private ?int $numeroSeance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesTherapeute = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    // Statuts : "En cours d'inscriptions", "Confirmé", "Annulé", "Effectué"
    #[ORM\Column(length: 50)]
    private ?string $statut = "En cours d'inscriptions";

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lienVisio = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateCreation = null;

    /**
     * @var Collection<int, InscriptionGroupe>
     */
    #[ORM\OneToMany(targetEntity: InscriptionGroupe::class, mappedBy: 'sessionGroupe', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $inscriptions;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->inscriptions = new ArrayCollection();
        $this->statut = "En cours d'inscriptions";
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
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

    public function getLienVisio(): ?string
    {
        return $this->lienVisio;
    }

    public function setLienVisio(?string $lienVisio): static
    {
        $this->lienVisio = $lienVisio;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    /**
     * @return Collection<int, InscriptionGroupe>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(InscriptionGroupe $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setSessionGroupe($this);
        }
        return $this;
    }

    public function removeInscription(InscriptionGroupe $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            if ($inscription->getSessionGroupe() === $this) {
                $inscription->setSessionGroupe(null);
            }
        }
        return $this;
    }

    public function getGroupe(): ?Groupe
    {
        return $this->groupe;
    }

    public function setGroupe(?Groupe $groupe): static
    {
        $this->groupe = $groupe;
        return $this;
    }

    public function getNumeroSeance(): ?int
    {
        return $this->numeroSeance;
    }

    public function setNumeroSeance(?int $numeroSeance): static
    {
        $this->numeroSeance = $numeroSeance;
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

    public function getNotesTherapeute(): ?string
    {
        return $this->notesTherapeute;
    }

    public function setNotesTherapeute(?string $notesTherapeute): static
    {
        $this->notesTherapeute = $notesTherapeute;
        return $this;
    }

    // --- HELPERS MÉTIER ---

    public function getNombreInscrits(): int
    {
        $count = 0;
        foreach ($this->inscriptions as $insc) {
            if (in_array($insc->getStatutPaiement(), ['Empreinte validée', 'Payé']) || $insc->getStatutPresence() === 'Confirmé') {
                $count++;
            }
        }
        return $count;
    }

    public function getNombreConfirmes(): int
    {
        $count = 0;
        foreach ($this->inscriptions as $insc) {
            if ($insc->getStatutPresence() === 'Confirmé') {
                $count++;
            }
        }
        return $count;
    }

    public function getNombreEnAttente(): int
    {
        $count = 0;
        foreach ($this->inscriptions as $insc) {
            if ($insc->getStatutPresence() === 'En attente') {
                $count++;
            }
        }
        return $count;
    }

    public function getNombreDeclines(): int
    {
        $count = 0;
        foreach ($this->inscriptions as $insc) {
            if ($insc->getStatutPresence() === 'Décliné') {
                $count++;
            }
        }
        return $count;
    }

    public function getSeuilMinimum(): int
    {
        return $this->prestation ? $this->prestation->getSeuilMinimum() : 5;
    }

    public function getCapaciteMaximale(): int
    {
        return $this->prestation ? $this->prestation->getCapaciteMaximale() : 10;
    }

    public function isComplet(): bool
    {
        return $this->getNombreInscrits() >= $this->getCapaciteMaximale();
    }

    public function isQuorumAtteint(): bool
    {
        return $this->getNombreInscrits() >= $this->getSeuilMinimum();
    }

    public function __toString(): string
    {
        $nom = $this->groupe ? $this->groupe->getNom() : ($this->prestation ? $this->prestation->getNom() : 'Accompagnement');
        $seanceLabel = $this->numeroSeance ? ' - Séance ' . $this->numeroSeance : '';
        $date = $this->dateDebut ? $this->dateDebut->format('d/m/Y H:i') : 'Date indéterminée';
        return sprintf('%s%s (%s) [%d/%d]', $nom, $seanceLabel, $date, $this->getNombreInscrits(), $this->getSeuilMinimum());
    }
}
