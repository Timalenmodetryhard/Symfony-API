<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250109182639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE dog (
                id SERIAL NOT NULL, 
                breed VARCHAR(255) NOT NULL,
                sub_breed VARCHAR(255),
                image VARCHAR(255) NOT NULL,
                UNIQUE (image),
                PRIMARY KEY(id)
            )
        ');
    }

    public function down(Schema $schema): void
    {
        // Suppression des tables dans l'ordre inverse pour éviter les erreurs de clés étrangères
        $this->addSql('DROP TABLE IF EXISTS dog');
    }
}
