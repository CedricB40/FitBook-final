<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
#[ORM\Table(name: 'cours')]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_cours')]
    private ?int $id = null;

    #[ORM\Column(name: 'titre_cours', length: 100)]
    private ?string $titre = null;

    #[ORM\Column(name: 'description_cours', type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'duree_cours')]
    private ?int $duree = null;

    #[ORM\Column(name: 'capacite_cours')]
    private ?int $capacite = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_salle', referencedColumnName: 'id_salle', nullable: false)]
    private ?Salle $salle = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_activite', referencedColumnName: 'id_activite', nullable: false)]
    private ?Activite $activite = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

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

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(int $capacite): static
    {
        $this->capacite = $capacite;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(Salle $salle): static
    {
        $this->salle = $salle;

        return $this;
    }

    public function getActivite(): ?Activite
    {
        return $this->activite;
    }

    public function setActivite(Activite $activite): static
    {
        $this->activite = $activite;

        return $this;
    }
}
