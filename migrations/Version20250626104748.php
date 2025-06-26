<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250626104748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact_settings (id INT AUTO_INCREMENT NOT NULL, title_bg VARCHAR(255) DEFAULT NULL, title_en VARCHAR(255) DEFAULT NULL, title_de VARCHAR(255) DEFAULT NULL, title_ru VARCHAR(255) DEFAULT NULL, subtitle_bg LONGTEXT DEFAULT NULL, subtitle_en LONGTEXT DEFAULT NULL, subtitle_de LONGTEXT DEFAULT NULL, subtitle_ru LONGTEXT DEFAULT NULL, company_name VARCHAR(255) DEFAULT NULL, address LONGTEXT DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, working_hours VARCHAR(255) DEFAULT NULL, map_lat NUMERIC(10, 8) DEFAULT NULL, map_lng NUMERIC(11, 8) DEFAULT NULL, social_media JSON DEFAULT NULL, meta_title_bg VARCHAR(255) DEFAULT NULL, meta_title_en VARCHAR(255) DEFAULT NULL, meta_title_de VARCHAR(255) DEFAULT NULL, meta_title_ru VARCHAR(255) DEFAULT NULL, meta_description_bg LONGTEXT DEFAULT NULL, meta_description_en LONGTEXT DEFAULT NULL, meta_description_de LONGTEXT DEFAULT NULL, meta_description_ru LONGTEXT DEFAULT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE contact_settings');
    }
}
