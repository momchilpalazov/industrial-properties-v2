<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250628170757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contributor_profiles ADD country VARCHAR(2) DEFAULT NULL, ADD city VARCHAR(255) DEFAULT NULL, ADD professional_background VARCHAR(100) DEFAULT NULL, ADD experience VARCHAR(50) DEFAULT NULL, ADD motivation LONGTEXT DEFAULT NULL, ADD agree_to_terms TINYINT(1) NOT NULL, ADD subscribe_newsletter TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contributor_profiles DROP country, DROP city, DROP professional_background, DROP experience, DROP motivation, DROP agree_to_terms, DROP subscribe_newsletter');
    }
}
