<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавяне на многоезични полета за екипа в about_settings
 */
final class Version20250625204000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавяне на многоезични полета за екипа в about_settings таблицата';
    }

    public function up(Schema $schema): void
    {
        // Добавяме новите колони без default стойности
        $this->addSql('ALTER TABLE about_settings ADD team_bg JSON NOT NULL');
        $this->addSql('ALTER TABLE about_settings ADD team_en JSON NOT NULL');
        $this->addSql('ALTER TABLE about_settings ADD team_de JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE about_settings ADD team_ru JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE about_settings ADD team_common JSON NOT NULL');
        
        // Задаваме празни масиви като стойности
        $this->addSql('UPDATE about_settings SET team_bg = \'[]\', team_en = \'[]\', team_de = \'[]\', team_ru = \'[]\', team_common = \'[]\' WHERE id IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE about_settings DROP team_bg');
        $this->addSql('ALTER TABLE about_settings DROP team_en');
        $this->addSql('ALTER TABLE about_settings DROP team_de');
        $this->addSql('ALTER TABLE about_settings DROP team_ru');
        $this->addSql('ALTER TABLE about_settings DROP team_common');
    }
}
