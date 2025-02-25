<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250225184500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add response column to property_inquiry table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE property_inquiry ADD response LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE property_inquiry DROP response');
    }
} 