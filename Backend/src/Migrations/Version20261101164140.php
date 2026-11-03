<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261101164140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table salles liée à son gestionnaire';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE salles (id_salle INT AUTO_INCREMENT NOT NULL, nom_salle VARCHAR(100) NOT NULL, adresse_salle VARCHAR(255) NOT NULL, ville_salle VARCHAR(50) NOT NULL, description_salle LONGTEXT DEFAULT NULL, telephone_salle VARCHAR(255) DEFAULT NULL, photo_salle VARCHAR(255) DEFAULT NULL, id_utilisateur INT NOT NULL, UNIQUE INDEX UNIQ_799D45AA50EAE44 (id_utilisateur), PRIMARY KEY (id_salle)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE salles ADD CONSTRAINT FK_799D45AA50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id_utilisateur)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salles DROP FOREIGN KEY FK_799D45AA50EAE44');
        $this->addSql('DROP TABLE salles');
    }
}
