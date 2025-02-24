<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250219130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавяне на нови статуси за имотите';
    }

    public function up(Schema $schema): void
    {
        // Първо запазваме текущите данни
        $this->addSql('UPDATE properties SET status = "available" WHERE status NOT IN ("available", "sold", "reserved", "rented", "pending")');
    }

    public function down(Schema $schema): void
    {
        // Връщаме статусите към старите стойности
        $this->addSql('UPDATE properties SET status = "available" WHERE status NOT IN ("available", "unavailable")');
    }
} 