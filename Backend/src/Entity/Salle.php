<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
#[ORM\Table(name: 'salles')]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_salle')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom_salle', length: 100)]
    private ?string $nom = null;

    #[ORM\Column(name: 'adresse_salle', length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(name: 'ville_salle', length: 50)]
    private ?string $ville = null;

    #[ORM\Column(name: 'description_salle', type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'telephone_salle', length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(name: 'photo_salle', length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id_utilisateur', nullable: false, unique: true)]
    private ?Utilisateur $gestionnaire = null;

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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getGestionnaire(): ?Utilisateur
    {
        return $this->gestionnaire;
    }

    public function setGestionnaire(Utilisateur $gestionnaire): static
    {
        $this->gestionnaire = $gestionnaire;

        return $this;
    }
}