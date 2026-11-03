<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261101161250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table activites';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activites (id_activite INT AUTO_INCREMENT NOT NULL, nom_activite VARCHAR(255) NOT NULL, description_activite LONGTEXT NOT NULL, PRIMARY KEY (id_activite)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE activites');
    }
}
