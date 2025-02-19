<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250219120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Създаване на таблица за FAQ';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE faqs (
            id INT AUTO_INCREMENT NOT NULL,
            question_bg VARCHAR(255) NOT NULL,
            question_en VARCHAR(255) NOT NULL,
            answer_bg TEXT NOT NULL,
            answer_en TEXT NOT NULL,
            category VARCHAR(50) NOT NULL,
            position INT NOT NULL,
            is_active TINYINT(1) NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE faqs');
    }
} 