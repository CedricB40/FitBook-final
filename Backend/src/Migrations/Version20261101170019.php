<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261101170019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table seances liée à son cours et à son coach';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE seances (id_seance INT AUTO_INCREMENT NOT NULL, date_heure_debut DATETIME NOT NULL, date_heure_fin DATETIME NOT NULL, id_cours INT NOT NULL, id_coach INT NOT NULL, INDEX IDX_FC699FF1134FCDAC (id_cours), INDEX IDX_FC699FF1D1DC2CFC (id_coach), PRIMARY KEY (id_seance)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE seances ADD CONSTRAINT FK_FC699FF1134FCDAC FOREIGN KEY (id_cours) REFERENCES cours (id_cours)');
        $this->addSql('ALTER TABLE seances ADD CONSTRAINT FK_FC699FF1D1DC2CFC FOREIGN KEY (id_coach) REFERENCES coachs (id_coach)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seances DROP FOREIGN KEY FK_FC699FF1134FCDAC');
        $this->addSql('ALTER TABLE seances DROP FOREIGN KEY FK_FC699FF1D1DC2CFC');
        $this->addSql('DROP TABLE seances');
    }
}
