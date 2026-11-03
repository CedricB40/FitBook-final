<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261101171849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression en cascade du profil coach et de la salle avec leur utilisateur';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coachs DROP FOREIGN KEY `FK_89E318FD50EAE44`');
        $this->addSql('ALTER TABLE coachs ADD CONSTRAINT FK_89E318FD50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id_utilisateur) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE salles DROP FOREIGN KEY `FK_799D45AA50EAE44`');
        $this->addSql('ALTER TABLE salles ADD CONSTRAINT FK_799D45AA50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id_utilisateur) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coachs DROP FOREIGN KEY FK_89E318FD50EAE44');
        $this->addSql('ALTER TABLE coachs ADD CONSTRAINT `FK_89E318FD50EAE44` FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id_utilisateur) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE salles DROP FOREIGN KEY FK_799D45AA50EAE44');
        $this->addSql('ALTER TABLE salles ADD CONSTRAINT `FK_799D45AA50EAE44` FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id_utilisateur) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
