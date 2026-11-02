<?php

namespace App\Entity;

use App\Repository\AvisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
#[ORM\Table(name: 'avis')]
#[ORM\UniqueConstraint(name: 'UNIQ_AVIS_RESERVATION_CIBLE', columns: ['id_reservation', 'cible_avis'])]
class Avis
{
    public const CIBLE_SALLE = 'SALLE';
    public const CIBLE_COACH = 'COACH';
    public const CIBLE_COURS = 'COURS';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_avis')]
    private ?int $id = null;

    #[ORM\Column(name: 'cible_avis', length: 50)]
    private ?string $cible = null;

    #[ORM\Column(name: 'note_avis')]
    private ?int $note = null;

    #[ORM\Column(name: 'commentaire_avis', type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(name: 'date_avis', type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $dateAvis = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_reservation', referencedColumnName: 'id_reservation', nullable: false, onDelete: 'CASCADE')]
    private ?Reservation $reservation = null;

    public function __construct()
    {
        $this->dateAvis = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCible(): ?string
    {
        return $this->cible;
    }

    public function setCible(string $cible): static
    {
        $this->cible = $cible;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getDateAvis(): ?\DateTimeImmutable
    {
        return $this->dateAvis;
    }

    public function setDateAvis(\DateTimeImmutable $dateAvis): static
    {
        $this->dateAvis = $dateAvis;

        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(Reservation $reservation): static
    {
        $this->reservation = $reservation;

        return $this;
    }
}
