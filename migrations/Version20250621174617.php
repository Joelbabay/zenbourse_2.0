<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250621174617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le champ slug à l\'entité Menu avec génération automatique des slugs existants';
    }

    public function up(Schema $schema): void
    {
        // Ajoute d'abord la colonne sans contrainte unique
        $this->addSql('ALTER TABLE menu ADD slug VARCHAR(255) DEFAULT NULL');

        // Génère des slugs temporaires pour les entrées existantes
        $this->addSql('UPDATE menu SET slug = CONCAT("temp-", id) WHERE slug IS NULL OR slug = ""');

        // Ajoute maintenant la contrainte unique
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D053A93989D9B62 ON menu (slug)');
        // On ne met pas NOT NULL pour permettre la régénération
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_7D053A93989D9B62 ON menu');
        $this->addSql('ALTER TABLE menu DROP slug');
    }
}
