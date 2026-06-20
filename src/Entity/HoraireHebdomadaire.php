<?php

namespace App\Entity;

use App\Repository\HoraireHebdomadaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HoraireHebdomadaireRepository::class)]
class HoraireHebdomadaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $jour = null;

    #[ORM\Column]
    private ?bool $estOuvert = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $ouvertureMatin = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $fermetureMatin = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $ouvertureApresMidi = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $fermetureApresMidi = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJour(): ?int
    {
        return $this->jour;
    }

    public function setJour(int $jour): static
    {
        $this->jour = $jour;

        return $this;
    }

    public function isEstOuvert(): ?bool
    {
        return $this->estOuvert;
    }

    public function setEstOuvert(bool $estOuvert): static
    {
        $this->estOuvert = $estOuvert;

        return $this;
    }

    public function getOuvertureMatin(): ?\DateTime
    {
        return $this->ouvertureMatin;
    }

    public function setOuvertureMatin(?\DateTime $ouvertureMatin): static
    {
        $this->ouvertureMatin = $ouvertureMatin;

        return $this;
    }

    public function getFermetureMatin(): ?\DateTime
    {
        return $this->fermetureMatin;
    }

    public function setFermetureMatin(?\DateTime $fermetureMatin): static
    {
        $this->fermetureMatin = $fermetureMatin;

        return $this;
    }

    public function getOuvertureApresMidi(): ?\DateTime
    {
        return $this->ouvertureApresMidi;
    }

    public function setOuvertureApresMidi(?\DateTime $ouvertureApresMidi): static
    {
        $this->ouvertureApresMidi = $ouvertureApresMidi;

        return $this;
    }

    public function getFermetureApresMidi(): ?\DateTime
    {
        return $this->fermetureApresMidi;
    }

    public function setFermetureApresMidi(?\DateTime $fermetureApresMidi): static
    {
        $this->fermetureApresMidi = $fermetureApresMidi;

        return $this;
    }
}
