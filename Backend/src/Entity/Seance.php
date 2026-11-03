<?php

namespace App\Entity;

use App\Repository\SeanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeanceRepository::class)]
#[ORM\Table(name: 'seances')]
class Seance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_seance')]
    private ?int $id = null;

    #[ORM\Column(name: 'date_heure_debut', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateHeureDebut = null;

    #[ORM\Column(name: 'date_heure_fin', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateHeureFin = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_cours', referencedColumnName: 'id_cours', nullable: false)]
    private ?Cours $cours = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_coach', referencedColumnName: 'id_coach', nullable: false)]
    private ?Coach $coach = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateHeureDebut(): ?\DateTimeImmutable
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeImmutable $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDateHeureFin(): ?\DateTimeImmutable
    {
        return $this->dateHeureFin;
    }

    public function setDateHeureFin(\DateTimeImmutable $dateHeureFin): static
    {
        $this->dateHeureFin = $dateHeureFin;

        return $this;
    }

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function setCours(Cours $cours): static
    {
        $this->cours = $cours;

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
