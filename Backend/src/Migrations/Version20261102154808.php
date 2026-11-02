<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261102154808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables favoris_salles, favoris_coachs et favoris_cours';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favoris_salles (id_utilisateur INT NOT NULL, id_salle INT NOT NULL, INDEX IDX_43FB711750EAE44 (id_utilisateur), INDEX IDX_43FB7117A0123F6C (id_salle), PRIMARY KEY (id_utilisateur, id_salle)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE favoris_coachs (id_utilisateur INT NOT NULL, id_coach INT NOT NULL, INDEX IDX_B3852C4050EAE44 (id_utilisateur), INDEX IDX_B3852C40D1DC2CFC (id_coach), PRIMARY KEY (id_utilisateur, id_coach)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE favoris_cours (id_utilisateur INT NOT NULL, id_cours INT NOT NULL, INDEX IDX_2EAFFFFD50EAE44 (id_utilisateur), INDEX IDX_2EAFFFFD134FCDAC (id_cours), PRIMARY KEY (id_utilisateur, id_cours)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE favoris_salles ADD CONSTRAINT FK_43FB711750EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id_utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favoris_salles ADD CONSTRAINT FK_43FB7117A0123F6C FOREIGN KEY (id_salle) REFERENCES salles (id_salle) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favoris_coachs ADD CONSTRAINT FK_B3852C4050EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id_utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favoris_coachs ADD CONSTRAINT FK_B3852C40D1DC2CFC FOREIGN KEY (id_coach) REFERENCES coachs (id_coach) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favoris_cours ADD CONSTRAINT FK_2EAFFFFD50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id_utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE favoris_cours ADD CONSTRAINT FK_2EAFFFFD134FCDAC FOREIGN KEY (id_cours) REFERENCES cours (id_cours) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favoris_salles DROP FOREIGN KEY FK_43FB711750EAE44');
        $this->addSql('ALTER TABLE favoris_salles DROP FOREIGN KEY FK_43FB7117A0123F6C');
        $this->addSql('ALTER TABLE favoris_coachs DROP FOREIGN KEY FK_B3852C4050EAE44');
        $this->addSql('ALTER TABLE favoris_coachs DROP FOREIGN KEY FK_B3852C40D1DC2CFC');
        $this->addSql('ALTER TABLE favoris_cours DROP FOREIGN KEY FK_2EAFFFFD50EAE44');
        $this->addSql('ALTER TABLE favoris_cours DROP FOREIGN KEY FK_2EAFFFFD134FCDAC');
        $this->addSql('DROP TABLE favoris_salles');
        $this->addSql('DROP TABLE favoris_coachs');
        $this->addSql('DROP TABLE favoris_cours');
    }
}
