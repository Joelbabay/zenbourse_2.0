<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250814151621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page_content ADD stock_example_id INT DEFAULT NULL, CHANGE menu_id menu_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE page_content ADD CONSTRAINT FK_4A5DB3C54E30BD FOREIGN KEY (stock_example_id) REFERENCES stock_example (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4A5DB3C54E30BD ON page_content (stock_example_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page_content DROP FOREIGN KEY FK_4A5DB3C54E30BD');
        $this->addSql('DROP INDEX UNIQ_4A5DB3C54E30BD ON page_content');
        $this->addSql('ALTER TABLE page_content DROP stock_example_id, CHANGE menu_id menu_id INT NOT NULL');
    }
}
