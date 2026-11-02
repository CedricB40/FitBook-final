<?php

namespace App\Controller;

use App\Entity\Coach;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProcedureController extends AbstractController
{
    #[Route(path: '/api/seances/{id}/places', name: 'api_seances_places', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function placesRestantes(int $id, Connection $connection): JsonResponse
    {
        $connection->executeStatement('CALL getPlacesRestantes(?, @places)', [$id]);
        $places = $connection->fetchOne('SELECT @places');

        if ($places === null || $places === false) {
            return $this->json(['erreur' => 'Séance introuvable.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'seance' => $id,
            'placesRestantes' => (int) $places,
        ]);
    }

    #[Route(path: '/api/coach/statistiques', name: 'api_coach_statistiques', methods: ['GET'])]
    public function statistiquesCoach(Connection $connection, EntityManagerInterface $entityManager): JsonResponse
    {
        $coach = $entityManager->getRepository(Coach::class)->findOneBy(['utilisateur' => $this->getUser()]);

        if (!$coach) {
            return $this->json(['erreur' => 'Aucun profil coach enregistré pour cet utilisateur.'], Response::HTTP_NOT_FOUND);
        }

        $statistiques = $connection->fetchAssociative('CALL getStatistiquesCoach(?)', [$coach->getId()]);

        return $this->json([
            'seancesAVenir' => (int) ($statistiques['seances_a_venir'] ?? 0),
            'reservationsConfirmees' => (int) ($statistiques['reservations_confirmees'] ?? 0),
            'tauxRemplissage' => (float) ($statistiques['taux_remplissage'] ?? 0),
        ]);
    }

    #[Route(path: '/api/sportif/historique', name: 'api_sportif_historique', methods: ['GET'])]
    public function historiqueSportif(Connection $connection): JsonResponse
    {
        $lignes = $connection->fetchAllAssociative('CALL getHistoriqueSportif(?)', [$this->getUser()->getId()]);

        return $this->json(array_map(
            fn (array $ligne) => [
                'id' => (int) $ligne['id_reservation'],
                'statut' => $ligne['statut_reservation'],
                'dateReservation' => (new \DateTimeImmutable($ligne['date_reservation']))->format('Y-m-d\TH:i:s'),
                'debut' => (new \DateTimeImmutable($ligne['date_heure_debut']))->format('Y-m-d\TH:i:s'),
                'fin' => (new \DateTimeImmutable($ligne['date_heure_fin']))->format('Y-m-d\TH:i:s'),
                'cours' => $ligne['titre_cours'],
                'salle' => $ligne['nom_salle'],
                'ville' => $ligne['ville_salle'],
            ],
            $lignes
        ));
    }
}
