<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261102172607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables disponibilites et horaires';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE disponibilites (id_disponibilite INT AUTO_INCREMENT NOT NULL, jour_semaine SMALLINT NOT NULL, heure_debut TIME NOT NULL, heure_fin TIME NOT NULL, id_coach INT NOT NULL, INDEX IDX_B0F3489CD1DC2CFC (id_coach), PRIMARY KEY (id_disponibilite)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE horaires (id_horaire INT AUTO_INCREMENT NOT NULL, jour_semaine SMALLINT NOT NULL, heure_ouverture TIME NOT NULL, heure_fermeture TIME NOT NULL, id_salle INT NOT NULL, INDEX IDX_39B7118FA0123F6C (id_salle), PRIMARY KEY (id_horaire)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE disponibilites ADD CONSTRAINT FK_B0F3489CD1DC2CFC FOREIGN KEY (id_coach) REFERENCES coachs (id_coach) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE horaires ADD CONSTRAINT FK_39B7118FA0123F6C FOREIGN KEY (id_salle) REFERENCES salles (id_salle) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE disponibilites DROP FOREIGN KEY FK_B0F3489CD1DC2CFC');
        $this->addSql('ALTER TABLE horaires DROP FOREIGN KEY FK_39B7118FA0123F6C');
        $this->addSql('DROP TABLE disponibilites');
        $this->addSql('DROP TABLE horaires');
    }
}
