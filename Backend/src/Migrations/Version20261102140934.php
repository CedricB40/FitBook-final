<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102140934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des procédures stockées getPlacesRestantes, getStatistiquesCoach et getHistoriqueSportif';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP PROCEDURE IF EXISTS getPlacesRestantes');
        $this->addSql(<<<'SQL'
CREATE PROCEDURE getPlacesRestantes(IN idSeance INT, OUT places INT)
BEGIN
    SELECT GREATEST(c.capacite_cours - COUNT(r.id_reservation), 0) INTO places
    FROM seances s
    INNER JOIN cours c ON c.id_cours = s.id_cours
    LEFT JOIN reservations r ON r.id_seance = s.id_seance AND r.statut_reservation = 'CONFIRMEE'
    WHERE s.id_seance = idSeance
    GROUP BY c.capacite_cours;
END
SQL);

        $this->addSql('DROP PROCEDURE IF EXISTS getStatistiquesCoach');
        $this->addSql(<<<'SQL'
CREATE PROCEDURE getStatistiquesCoach(IN idCoach INT)
BEGIN
    SELECT
        COUNT(*) AS seances_a_venir,
        COALESCE(SUM(t.reservees), 0) AS reservations_confirmees,
        COALESCE(ROUND(100 * SUM(t.reservees) / NULLIF(SUM(t.capacite), 0), 1), 0) AS taux_remplissage
    FROM (
        SELECT
            s.id_seance,
            c.capacite_cours AS capacite,
            (
                SELECT COUNT(*)
                FROM reservations r
                WHERE r.id_seance = s.id_seance AND r.statut_reservation = 'CONFIRMEE'
            ) AS reservees
        FROM seances s
        INNER JOIN cours c ON c.id_cours = s.id_cours
        WHERE s.id_coach = idCoach AND s.date_heure_debut >= NOW()
    ) AS t;
END
SQL);

        $this->addSql('DROP PROCEDURE IF EXISTS getHistoriqueSportif');
        $this->addSql(<<<'SQL'
CREATE PROCEDURE getHistoriqueSportif(IN idUtilisateur INT)
BEGIN
    SELECT
        r.id_reservation,
        r.statut_reservation,
        r.date_reservation,
        s.date_heure_debut,
        s.date_heure_fin,
        c.titre_cours,
        sa.nom_salle,
        sa.ville_salle
    FROM reservations r
    INNER JOIN seances s ON s.id_seance = r.id_seance
    INNER JOIN cours c ON c.id_cours = s.id_cours
    INNER JOIN salles sa ON sa.id_salle = c.id_salle
    WHERE r.id_utilisateur = idUtilisateur
    ORDER BY s.date_heure_debut DESC;
END
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP PROCEDURE IF EXISTS getHistoriqueSportif');
        $this->addSql('DROP PROCEDURE IF EXISTS getStatistiquesCoach');
        $this->addSql('DROP PROCEDURE IF EXISTS getPlacesRestantes');
    }
}
