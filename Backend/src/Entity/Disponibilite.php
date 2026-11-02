<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Créneau hebdomadaire pendant lequel un coach est disponible (1 = lundi ... 7 = dimanche).
 */
#[ORM\Entity]
#[ORM\Table(name: 'disponibilites')]
class Disponibilite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_disponibilite')]
    private ?int $id = null;

    #[ORM\Column(name: 'jour_semaine', type: Types::SMALLINT)]
    private ?int $jourSemaine = null;

    #[ORM\Column(name: 'heure_debut', type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $heureDebut = null;

    #[ORM\Column(name: 'heure_fin', type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $heureFin = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_coach', referencedColumnName: 'id_coach', nullable: false, onDelete: 'CASCADE')]
    private ?Coach $coach = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJourSemaine(): ?int
    {
        return $this->jourSemaine;
    }

    public function setJourSemaine(int $jourSemaine): static
    {
        $this->jourSemaine = $jourSemaine;

        return $this;
    }

    public function getHeureDebut(): ?\DateTimeImmutable
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(\DateTimeImmutable $heureDebut): static
    {
        $this->heureDebut = $heureDebut;

        return $this;
    }

    public function getHeureFin(): ?\DateTimeImmutable
    {
        return $this->heureFin;
    }

    public function setHeureFin(\DateTimeImmutable $heureFin): static
    {
        $this->heureFin = $heureFin;

        return $this;
    }

    public function getCoach(): ?Coach
    {
        return $this->coach;
    }

    public function setCoach(Coach $coach): static
    {
        $this->coach = $coach;

        return $this;
    }
}
