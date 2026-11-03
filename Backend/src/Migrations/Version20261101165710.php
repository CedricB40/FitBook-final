<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261101165710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table cours liée à sa salle et à son activité';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cours (id_cours INT AUTO_INCREMENT NOT NULL, titre_cours VARCHAR(100) NOT NULL, description_cours LONGTEXT DEFAULT NULL, duree_cours INT NOT NULL, capacite_cours INT NOT NULL, id_salle INT NOT NULL, id_activite INT NOT NULL, INDEX IDX_FDCA8C9CA0123F6C (id_salle), INDEX IDX_FDCA8C9CE8AEB980 (id_activite), PRIMARY KEY (id_cours)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CA0123F6C FOREIGN KEY (id_salle) REFERENCES salles (id_salle)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CE8AEB980 FOREIGN KEY (id_activite) REFERENCES activites (id_activite)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CA0123F6C');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CE8AEB980');
        $this->addSql('DROP TABLE cours');
    }
}
