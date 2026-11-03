<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservations')]
#[ORM\UniqueConstraint(name: 'UNIQ_RESERVATIONS_SPORTIF_SEANCE', columns: ['id_utilisateur', 'id_seance'])]
class Reservation
{
    public const STATUT_CONFIRMEE = 'CONFIRMEE';
    public const STATUT_ANNULEE = 'ANNULEE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_reservation')]
    private ?int $id = null;

    #[ORM\Column(name: 'date_reservation', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateReservation = null;

    #[ORM\Column(name: 'statut_reservation', length: 100)]
    private ?string $statut = self::STATUT_CONFIRMEE;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id_utilisateur', nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $sportif = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_seance', referencedColumnName: 'id_seance', nullable: false, onDelete: 'CASCADE')]
    private ?Seance $seance = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateReservation(): ?\DateTimeImmutable
    {
        return $this->dateReservation;
    }

    public function setDateReservation(\DateTimeImmutable $dateReservation): static
    {
        $this->dateReservation = $dateReservation;

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

    public function getSportif(): ?Utilisateur
    {
        return $this->sportif;
    }

    public function setSportif(Utilisateur $sportif): static
    {
        $this->sportif = $sportif;

        return $this;
    }

    public function getSeance(): ?Seance
    {
        return $this->seance;
    }

    public function setSeance(Seance $seance): static
    {
        $this->seance = $seance;

        return $this;
    }
}
