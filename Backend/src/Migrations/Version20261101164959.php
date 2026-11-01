<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261101164959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table coachs liée à son utilisateur';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE coachs (id_coach INT AUTO_INCREMENT NOT NULL, biographie_coach LONGTEXT DEFAULT NULL, specialite_coach VARCHAR(255) NOT NULL, photo_coach VARCHAR(255) DEFAULT NULL, id_utilisateur INT NOT NULL, UNIQUE INDEX UNIQ_89E318FD50EAE44 (id_utilisateur), PRIMARY KEY (id_coach)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE coachs ADD CONSTRAINT FK_89E318FD50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id_utilisateur)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coachs DROP FOREIGN KEY FK_89E318FD50EAE44');
        $this->addSql('DROP TABLE coachs');
    }
}
