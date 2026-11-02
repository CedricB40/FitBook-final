<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Plage d'ouverture hebdomadaire d'une salle (1 = lundi ... 7 = dimanche).
 * Un jour sans plage correspond à un jour de fermeture.
 */
#[ORM\Entity]
#[ORM\Table(name: 'horaires')]
class Horaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_horaire')]
    private ?int $id = null;

    #[ORM\Column(name: 'jour_semaine', type: Types::SMALLINT)]
    private ?int $jourSemaine = null;

    #[ORM\Column(name: 'heure_ouverture', type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $heureOuverture = null;

    #[ORM\Column(name: 'heure_fermeture', type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $heureFermeture = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_salle', referencedColumnName: 'id_salle', nullable: false, onDelete: 'CASCADE')]
    private ?Salle $salle = null;

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

    public function getHeureOuverture(): ?\DateTimeImmutable
    {
        return $this->heureOuverture;
    }

    public function setHeureOuverture(\DateTimeImmutable $heureOuverture): static
    {
        $this->heureOuverture = $heureOuverture;

        return $this;
    }

    public function getHeureFermeture(): ?\DateTimeImmutable
    {
        return $this->heureFermeture;
    }

    public function setHeureFermeture(\DateTimeImmutable $heureFermeture): static
    {
        $this->heureFermeture = $heureFermeture;

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
}
