<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261102155904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table avis';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avis (id_avis INT AUTO_INCREMENT NOT NULL, cible_avis VARCHAR(50) NOT NULL, note_avis INT NOT NULL, commentaire_avis LONGTEXT DEFAULT NULL, date_avis DATE NOT NULL, id_reservation INT NOT NULL, UNIQUE INDEX UNIQ_AVIS_RESERVATION_CIBLE (id_reservation, cible_avis), INDEX IDX_8F91ABF05ADA84A2 (id_reservation), PRIMARY KEY (id_avis)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF05ADA84A2 FOREIGN KEY (id_reservation) REFERENCES reservations (id_reservation) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF05ADA84A2');
        $this->addSql('DROP TABLE avis');
    }
}
