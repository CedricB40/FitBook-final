<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Cours;
use App\Entity\Reservation;
use App\Entity\Seance;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EspaceCoachController extends AbstractController
{
    private const FORMAT_ISO = 'Y-m-d\TH:i:s';

    #[Route(path: '/api/coach/planning', name: 'api_coach_planning', methods: ['GET'])]
    public function planning(EntityManagerInterface $entityManager): JsonResponse
    {
        $coach = $this->coach($entityManager);

        if (!$coach) {
            return $this->profilManquant();
        }

        $seances = $entityManager->createQuery(
            'SELECT se, c, s, a
             FROM App\Entity\Seance se
             JOIN se.cours c
             JOIN c.salle s
             JOIN c.activite a
             WHERE se.coach = :coach AND se.dateHeureDebut >= :depuis
             ORDER BY se.dateHeureDebut ASC'
        )
            ->setParameter('coach', $coach)
            ->setParameter('depuis', new \DateTimeImmutable('-30 days'))
            ->getResult();

        $reservees = $this->compterReservations($entityManager, $seances);

        return $this->json(array_map(
            fn (Seance $seance) => $this->formaterSeance($seance, $reservees[$seance->getId()] ?? 0),
            $seances
        ));
    }

    #[Route(path: '/api/coach/cours', name: 'api_coach_cours', methods: ['GET'])]
    public function cours(EntityManagerInterface $entityManager): JsonResponse
    {
        $cours = $entityManager->createQuery(
            'SELECT c, s, a
             FROM App\Entity\Cours c
             JOIN c.salle s
             JOIN c.activite a
             ORDER BY c.titre ASC'
        )->getResult();

        return $this->json(array_map(
            fn (Cours $unCours) => [
                'id' => $unCours->getId(),
                'titre' => $unCours->getTitre(),
                'duree' => $unCours->getDuree(),
                'capacite' => $unCours->getCapacite(),
                'salle' => [
                    'id' => $unCours->getSalle()?->getId(),
                    'nom' => $unCours->getSalle()?->getNom(),
                    'ville' => $unCours->getSalle()?->getVille(),
                ],
                'activite' => $unCours->getActivite()?->getNom(),
            ],
            $cours
        ));
    }

    #[Route(path: '/api/coach/reservations', name: 'api_coach_reservations', methods: ['GET'])]
    public function reservations(EntityManagerInterface $entityManager): JsonResponse
    {
        $coach = $this->coach($entityManager);

        if (!$coach) {
            return $this->profilManquant();
        }

        $reservations = $entityManager->createQuery(
            'SELECT r, se, c, s, u
             FROM App\Entity\Reservation r
             JOIN r.seance se
             JOIN se.cours c
             JOIN c.salle s
             JOIN r.sportif u
             WHERE se.coach = :coach AND r.statut = :statut
             ORDER BY se.dateHeureDebut DESC'
        )
            ->setParameter('coach', $coach)
            ->setParameter('statut', Reservation::STATUT_CONFIRMEE)
            ->getResult();

        return $this->json(array_map(
            fn (Reservation $reservation) => [
                'id' => $reservation->getId(),
                'dateReservation' => $reservation->getDateReservation()?->format(self::FORMAT_ISO),
                'sportif' => [
                    'nom' => $reservation->getSportif()?->getNom(),
                    'prenom' => $reservation->getSportif()?->getPrenom(),
                    'email' => $reservation->getSportif()?->getEmail(),
                ],
                'seance' => [
                    'id' => $reservation->getSeance()?->getId(),
                    'debut' => $reservation->getSeance()?->getDateHeureDebut()?->format(self::FORMAT_ISO),
                    'fin' => $reservation->getSeance()?->getDateHeureFin()?->format(self::FORMAT_ISO),
                    'cours' => $reservation->getSeance()?->getCours()?->getTitre(),
                    'salle' => $reservation->getSeance()?->getCours()?->getSalle()?->getNom(),
                ],
            ],
            $reservations
        ));
    }

    private function coach(EntityManagerInterface $entityManager): ?Coach
    {
        return $entityManager->getRepository(Coach::class)->findOneBy(['utilisateur' => $this->getUser()]);
    }

    private function profilManquant(): JsonResponse
    {
        return $this->json(
            ['erreur' => "Tu dois d'abord enregistrer ton profil de coach."],
            Response::HTTP_CONFLICT
        );
    }

    private function compterReservations(EntityManagerInterface $entityManager, array $seances): array
    {
        if ($seances === []) {
            return [];
        }

        $lignes = $entityManager->createQuery(
            'SELECT IDENTITY(r.seance) AS seance, COUNT(r.id) AS total
             FROM App\Entity\Reservation r
             WHERE r.seance IN (:seances) AND r.statut = :statut
             GROUP BY seance'
        )
            ->setParameter('seances', $seances)
            ->setParameter('statut', Reservation::STATUT_CONFIRMEE)
            ->getResult();

        $comptes = [];

        foreach ($lignes as $ligne) {
            $comptes[(int) $ligne['seance']] = (int) $ligne['total'];
        }

        return $comptes;
    }

    private function formaterSeance(Seance $seance, int $reservees): array
    {
        $cours = $seance->getCours();
        $capacite = $cours?->getCapacite() ?? 0;

        return [
            'id' => $seance->getId(),
            'debut' => $seance->getDateHeureDebut()?->format(self::FORMAT_ISO),
            'fin' => $seance->getDateHeureFin()?->format(self::FORMAT_ISO),
            'cours' => [
                'id' => $cours?->getId(),
                'titre' => $cours?->getTitre(),
                'capacite' => $capacite,
                'activite' => $cours?->getActivite()?->getNom(),
            ],
            'salle' => [
                'id' => $cours?->getSalle()?->getId(),
                'nom' => $cours?->getSalle()?->getNom(),
                'ville' => $cours?->getSalle()?->getVille(),
            ],
            'reservees' => $reservees,
            'placesRestantes' => max(0, $capacite - $reservees),
        ];
    }
}
