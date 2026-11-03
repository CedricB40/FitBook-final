<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261102134716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table reservations';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservations (id_reservation INT AUTO_INCREMENT NOT NULL, date_reservation DATETIME NOT NULL, statut_reservation VARCHAR(100) NOT NULL, id_utilisateur INT NOT NULL, id_seance INT NOT NULL, UNIQUE INDEX UNIQ_RESERVATIONS_SPORTIF_SEANCE (id_utilisateur, id_seance), INDEX IDX_4DA23950EAE44 (id_utilisateur), INDEX IDX_4DA239F94A48E3 (id_seance), PRIMARY KEY (id_reservation)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA23950EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id_utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT FK_4DA239F94A48E3 FOREIGN KEY (id_seance) REFERENCES seances (id_seance) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA23950EAE44');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239F94A48E3');
        $this->addSql('DROP TABLE reservations');
    }
}
