<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250219115000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Премахване на колоната image от таблицата blog_posts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE blog_posts DROP COLUMN image');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE blog_posts ADD COLUMN image VARCHAR(255) NULL');
    }
} 