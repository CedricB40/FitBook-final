<?php

namespace App\Service;

use App\Entity\Avis;
use App\Entity\Reservation;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class NotesService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function notes(string $cible, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $expression = $this->expression($cible);

        $lignes = $this->entityManager->createQuery(
            "SELECT $expression AS cibleId, AVG(a.note) AS moyenne, COUNT(a.id) AS total
             FROM App\Entity\Avis a
             JOIN a.reservation r
             JOIN r.seance se
             JOIN se.cours c
             WHERE a.cible = :cible AND $expression IN (:ids)
             GROUP BY cibleId"
        )
            ->setParameter('cible', $cible)
            ->setParameter('ids', $ids)
            ->getResult();

        $notes = [];

        foreach ($lignes as $ligne) {
            $notes[(int) $ligne['cibleId']] = [
                'note' => round((float) $ligne['moyenne'], 1),
                'nbAvis' => (int) $ligne['total'],
            ];
        }

        return $notes;
    }

    public function resume(string $cible, int $id): array
    {
        $expression = $this->expression($cible);

        $lignes = $this->entityManager->createQuery(
            "SELECT a.note AS note, COUNT(a.id) AS total
             FROM App\Entity\Avis a
             JOIN a.reservation r
             JOIN r.seance se
             JOIN se.cours c
             WHERE a.cible = :cible AND $expression = :id
             GROUP BY a.note"
        )
            ->setParameter('cible', $cible)
            ->setParameter('id', $id)
            ->getResult();

        $repartition = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $total = 0;
        $somme = 0;

        foreach ($lignes as $ligne) {
            $repartition[(int) $ligne['note']] = (int) $ligne['total'];
            $total += (int) $ligne['total'];
            $somme += (int) $ligne['note'] * (int) $ligne['total'];
        }

        return [
            'moyenne' => $total > 0 ? round($somme / $total, 1) : null,
            'total' => $total,
            'repartition' => $repartition,
        ];
    }

    public function avis(string $cible, int $id, ?int $idUtilisateur): array
    {
        $expression = $this->expression($cible);

        $avis = $this->entityManager->createQuery(
            "SELECT a, r, se, c, u
             FROM App\Entity\Avis a
             JOIN a.reservation r
             JOIN r.seance se
             JOIN se.cours c
             JOIN r.sportif u
             WHERE a.cible = :cible AND $expression = :id
             ORDER BY a.dateAvis DESC, a.id DESC"
        )
            ->setParameter('cible', $cible)
            ->setParameter('id', $id)
            ->getResult();

        return array_map(
            fn (Avis $unAvis) => $this->formater($unAvis, $idUtilisateur),
            $avis
        );
    }

    public function formater(Avis $avis, ?int $idUtilisateur = null): array
    {
        $sportif = $avis->getReservation()?->getSportif();

        return [
            'id' => $avis->getId(),
            'auteur' => trim($sportif?->getPrenom() . ' ' . mb_substr((string) $sportif?->getNom(), 0, 1) . '.'),
            'note' => $avis->getNote(),
            'commentaire' => $avis->getCommentaire(),
            'date' => $avis->getDateAvis()?->format('Y-m-d'),
            'mien' => $idUtilisateur !== null && $sportif?->getId() === $idUtilisateur,
        ];
    }

    public function reservationEligible(Utilisateur $sportif, string $cible, int $id): ?Reservation
    {
        $expression = $this->expression($cible);

        return $this->entityManager->createQuery(
            "SELECT r
             FROM App\Entity\Reservation r
             JOIN r.seance se
             JOIN se.cours c
             WHERE r.sportif = :sportif
               AND r.statut = :statut
               AND se.dateHeureFin < :maintenant
               AND $expression = :id
               AND NOT EXISTS (
                   SELECT a1.id FROM App\Entity\Avis a1 WHERE a1.reservation = r AND a1.cible = :cible
               )
             ORDER BY se.dateHeureFin ASC"
        )
            ->setParameter('sportif', $sportif)
            ->setParameter('statut', Reservation::STATUT_CONFIRMEE)
            ->setParameter('maintenant', new \DateTimeImmutable())
            ->setParameter('id', $id)
            ->setParameter('cible', $cible)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    private function expression(string $cible): string
    {
        return match ($cible) {
            Avis::CIBLE_SALLE => 'IDENTITY(c.salle)',
            Avis::CIBLE_COACH => 'IDENTITY(se.coach)',
            Avis::CIBLE_COURS => 'c.id',
        };
    }
}
