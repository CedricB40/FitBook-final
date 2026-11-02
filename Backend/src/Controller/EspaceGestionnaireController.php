<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Salle;
use App\Entity\Seance;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EspaceGestionnaireController extends AbstractController
{
    private const FORMAT_ISO = 'Y-m-d\TH:i:s';

    #[Route(path: '/api/gestionnaire/planning', name: 'api_gestionnaire_planning', methods: ['GET'])]
    public function planning(EntityManagerInterface $entityManager): JsonResponse
    {
        $salle = $this->salle($entityManager);

        if (!$salle) {
            return $this->salleManquante();
        }

        $seances = $entityManager->createQuery(
            'SELECT se, c, a, co, u
             FROM App\Entity\Seance se
             JOIN se.cours c
             JOIN c.activite a
             JOIN se.coach co
             JOIN co.utilisateur u
             WHERE c.salle = :salle AND se.dateHeureDebut >= :depuis
             ORDER BY se.dateHeureDebut ASC'
        )
            ->setParameter('salle', $salle)
            ->setParameter('depuis', new \DateTimeImmutable('-30 days'))
            ->getResult();

        $reservees = $this->compterReservations($entityManager, $seances);

        return $this->json(array_map(function (Seance $seance) use ($reservees) {
            $capacite = $seance->getCours()?->getCapacite() ?? 0;
            $total = $reservees[$seance->getId()] ?? 0;

            return [
                'id' => $seance->getId(),
                'debut' => $seance->getDateHeureDebut()?->format(self::FORMAT_ISO),
                'fin' => $seance->getDateHeureFin()?->format(self::FORMAT_ISO),
                'cours' => [
                    'id' => $seance->getCours()?->getId(),
                    'titre' => $seance->getCours()?->getTitre(),
                    'capacite' => $capacite,
                    'activite' => $seance->getCours()?->getActivite()?->getNom(),
                ],
                'coachId' => $seance->getCoach()?->getId(),
                'coach' => $this->nomCoach($seance),
                'reservees' => $total,
                'placesRestantes' => max(0, $capacite - $total),
            ];
        }, $seances));
    }

    #[Route(path: '/api/gestionnaire/reservations', name: 'api_gestionnaire_reservations', methods: ['GET'])]
    public function reservations(EntityManagerInterface $entityManager): JsonResponse
    {
        $salle = $this->salle($entityManager);

        if (!$salle) {
            return $this->salleManquante();
        }

        $reservations = $entityManager->createQuery(
            'SELECT r, se, c, co, uc, us
             FROM App\Entity\Reservation r
             JOIN r.seance se
             JOIN se.cours c
             JOIN se.coach co
             JOIN co.utilisateur uc
             JOIN r.sportif us
             WHERE c.salle = :salle
             ORDER BY se.dateHeureDebut DESC'
        )
            ->setParameter('salle', $salle)
            ->getResult();

        return $this->json(array_map(
            fn (Reservation $reservation) => [
                'id' => $reservation->getId(),
                'statut' => $reservation->getStatut(),
                'dateReservation' => $reservation->getDateReservation()?->format(self::FORMAT_ISO),
                'sportif' => $this->abreger(
                    $reservation->getSportif()?->getPrenom(),
                    $reservation->getSportif()?->getNom()
                ),
                'seance' => [
                    'id' => $reservation->getSeance()?->getId(),
                    'debut' => $reservation->getSeance()?->getDateHeureDebut()?->format(self::FORMAT_ISO),
                    'cours' => $reservation->getSeance()?->getCours()?->getTitre(),
                    'coach' => $this->nomCoach($reservation->getSeance()),
                ],
            ],
            $reservations
        ));
    }

    #[Route(path: '/api/gestionnaire/coachs', name: 'api_gestionnaire_coachs', methods: ['GET'])]
    public function coachs(EntityManagerInterface $entityManager): JsonResponse
    {
        $salle = $this->salle($entityManager);

        if (!$salle) {
            return $this->salleManquante();
        }

        $seances = $entityManager->createQuery(
            'SELECT se, c, co, u
             FROM App\Entity\Seance se
             JOIN se.cours c
             JOIN se.coach co
             JOIN co.utilisateur u
             WHERE c.salle = :salle'
        )
            ->setParameter('salle', $salle)
            ->getResult();

        $coachs = [];

        foreach ($seances as $seance) {
            $coach = $seance->getCoach();
            $id = $coach->getId();

            if (!isset($coachs[$id])) {
                $coachs[$id] = [
                    'id' => $id,
                    'nom' => $this->nomCoach($seance),
                    'specialite' => $coach->getSpecialite(),
                    'photo' => $coach->getPhoto(),
                    'seances' => 0,
                ];
            }

            ++$coachs[$id]['seances'];
        }

        return $this->json(array_values($coachs));
    }

    private function salle(EntityManagerInterface $entityManager): ?Salle
    {
        return $entityManager->getRepository(Salle::class)->findOneBy(['gestionnaire' => $this->getUser()]);
    }

    private function salleManquante(): JsonResponse
    {
        return $this->json(
            ['erreur' => "Tu dois d'abord enregistrer ta salle."],
            Response::HTTP_CONFLICT
        );
    }

    private function nomCoach(?Seance $seance): string
    {
        $utilisateur = $seance?->getCoach()?->getUtilisateur();

        return $this->abreger($utilisateur?->getPrenom(), $utilisateur?->getNom());
    }

    private function abreger(?string $prenom, ?string $nom): string
    {
        $initiale = $nom ? mb_strtoupper(mb_substr($nom, 0, 1)).'.' : '';

        return trim(($prenom ?? '').' '.$initiale);
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
}
