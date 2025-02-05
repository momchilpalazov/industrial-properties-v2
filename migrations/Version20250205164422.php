<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250205164422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE about_settings ADD title_bg VARCHAR(255) NOT NULL, ADD title_en VARCHAR(255) NOT NULL, ADD title_de VARCHAR(255) DEFAULT NULL, ADD title_ru VARCHAR(255) DEFAULT NULL, ADD subtitle_bg VARCHAR(255) NOT NULL, ADD subtitle_en VARCHAR(255) NOT NULL, ADD subtitle_de VARCHAR(255) DEFAULT NULL, ADD subtitle_ru VARCHAR(255) DEFAULT NULL, ADD company_image VARCHAR(255) DEFAULT NULL, ADD values_bg JSON NOT NULL, ADD values_en JSON NOT NULL, ADD values_de JSON DEFAULT NULL, ADD values_ru JSON DEFAULT NULL, ADD team JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE about_settings DROP title_bg, DROP title_en, DROP title_de, DROP title_ru, DROP subtitle_bg, DROP subtitle_en, DROP subtitle_de, DROP subtitle_ru, DROP company_image, DROP values_bg, DROP values_en, DROP values_de, DROP values_ru, DROP team');
    }
}
