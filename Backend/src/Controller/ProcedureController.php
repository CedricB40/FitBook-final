<?php

namespace App\Controller;

use App\Entity\Coach;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Expose les 3 procédures stockées de FitBook.
 *
 * Certains hébergements mutualisés interdisent la création de procédures stockées.
 * Dans ce cas, chaque route exécute la requête SQL équivalente à la procédure,
 * ce qui donne exactement le même résultat.
 */
class ProcedureController extends AbstractController
{
    // Requêtes équivalentes aux procédures getPlacesRestantes, getStatistiquesCoach et getHistoriqueSportif.
    private const SQL_PLACES = <<<'SQL'
        SELECT GREATEST(c.capacite_cours - COUNT(r.id_reservation), 0)
        FROM seances s
        INNER JOIN cours c ON c.id_cours = s.id_cours
        LEFT JOIN reservations r ON r.id_seance = s.id_seance AND r.statut_reservation = 'CONFIRMEE'
        WHERE s.id_seance = ?
        GROUP BY c.capacite_cours
        SQL;

    private const SQL_STATISTIQUES = <<<'SQL'
        SELECT COUNT(*) AS seances_a_venir,
               COALESCE(SUM(t.reservees), 0) AS reservations_confirmees,
               COALESCE(ROUND(100 * SUM(t.reservees) / NULLIF(SUM(t.capacite), 0), 1), 0) AS taux_remplissage
        FROM (
            SELECT s.id_seance, c.capacite_cours AS capacite,
                   (SELECT COUNT(*) FROM reservations r
                    WHERE r.id_seance = s.id_seance AND r.statut_reservation = 'CONFIRMEE') AS reservees
            FROM seances s
            INNER JOIN cours c ON c.id_cours = s.id_cours
            WHERE s.id_coach = ? AND s.date_heure_debut >= NOW()
        ) AS t
        SQL;

    private const SQL_HISTORIQUE = <<<'SQL'
        SELECT r.id_reservation, r.statut_reservation, r.date_reservation,
               s.date_heure_debut, s.date_heure_fin, c.titre_cours, sa.nom_salle, sa.ville_salle
        FROM reservations r
        INNER JOIN seances s ON s.id_seance = r.id_seance
        INNER JOIN cours c ON c.id_cours = s.id_cours
        INNER JOIN salles sa ON sa.id_salle = c.id_salle
        WHERE r.id_utilisateur = ?
        ORDER BY s.date_heure_debut DESC
        SQL;

    #[Route(path: '/api/seances/{id}/places', name: 'api_seances_places', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function placesRestantes(int $id, Connection $connection): JsonResponse
    {
        try {
            $connection->executeStatement('CALL getPlacesRestantes(?, @places)', [$id]);
            $places = $connection->fetchOne('SELECT @places');
        } catch (DbalException $exception) {
            $this->verifierProcedureAbsente($exception);
            $places = $connection->fetchOne(self::SQL_PLACES, [$id]);
        }

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

        try {
            $statistiques = $connection->fetchAssociative('CALL getStatistiquesCoach(?)', [$coach->getId()]);
        } catch (DbalException $exception) {
            $this->verifierProcedureAbsente($exception);
            $statistiques = $connection->fetchAssociative(self::SQL_STATISTIQUES, [$coach->getId()]);
        }

        return $this->json([
            'seancesAVenir' => (int) ($statistiques['seances_a_venir'] ?? 0),
            'reservationsConfirmees' => (int) ($statistiques['reservations_confirmees'] ?? 0),
            'tauxRemplissage' => (float) ($statistiques['taux_remplissage'] ?? 0),
        ]);
    }

    #[Route(path: '/api/sportif/historique', name: 'api_sportif_historique', methods: ['GET'])]
    public function historiqueSportif(Connection $connection): JsonResponse
    {
        $idUtilisateur = $this->getUser()->getId();

        try {
            $lignes = $connection->fetchAllAssociative('CALL getHistoriqueSportif(?)', [$idUtilisateur]);
        } catch (DbalException $exception) {
            $this->verifierProcedureAbsente($exception);
            $lignes = $connection->fetchAllAssociative(self::SQL_HISTORIQUE, [$idUtilisateur]);
        }

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

    /**
     * Ne laisse passer que l'erreur « la procédure n'existe pas » : toute autre erreur est relancée.
     */
    private function verifierProcedureAbsente(DbalException $exception): void
    {
        if (!str_contains($exception->getMessage(), 'PROCEDURE')) {
            throw $exception;
        }
    }
}
