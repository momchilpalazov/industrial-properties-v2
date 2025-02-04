<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240204200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add latitude and longitude columns to properties table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE properties ADD latitude DECIMAL(10,8) DEFAULT NULL, ADD longitude DECIMAL(11,8) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE properties DROP latitude, DROP longitude');
    }
} 