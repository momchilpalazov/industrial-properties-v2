<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240204210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates blog_posts table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE blog_posts (
            id INT AUTO_INCREMENT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            slug VARCHAR(255) NOT NULL,
            language VARCHAR(2) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT "draft",
            views INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            updated_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            UNIQUE INDEX UNIQ_78B2F932989D9B62 (slug),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE blog_posts');
    }
} 