<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261101144555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table utilisateurs';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE utilisateurs (id_utilisateur INT AUTO_INCREMENT NOT NULL, nom_utilisateur VARCHAR(50) NOT NULL, prenom_utilisateur VARCHAR(50) NOT NULL, email_utilisateur VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, role_utilisateur VARCHAR(50) NOT NULL, email_confirme TINYINT DEFAULT 0 NOT NULL, date_inscription DATE NOT NULL, UNIQUE INDEX UNIQ_UTILISATEURS_EMAIL (email_utilisateur), PRIMARY KEY (id_utilisateur)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE utilisateurs');
    }
}
