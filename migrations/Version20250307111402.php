<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250307111402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property_types ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE property_types ADD CONSTRAINT FK_9F23483D727ACA70 FOREIGN KEY (parent_id) REFERENCES property_types (id)');
        $this->addSql('CREATE INDEX IDX_9F23483D727ACA70 ON property_types (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property_types DROP FOREIGN KEY FK_9F23483D727ACA70');
        $this->addSql('DROP INDEX IDX_9F23483D727ACA70 ON property_types');
        $this->addSql('ALTER TABLE property_types DROP parent_id');
    }
}
