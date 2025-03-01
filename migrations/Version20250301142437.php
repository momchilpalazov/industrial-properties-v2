<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301142437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE offer (id INT AUTO_INCREMENT NOT NULL, inquiry_id INT NOT NULL, property_id INT NOT NULL, customer_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(50) NOT NULL, price NUMERIC(10, 2) NOT NULL, status VARCHAR(20) NOT NULL, pdf_path VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_29D6873EA7AD6D71 (inquiry_id), INDEX IDX_29D6873E549213EC (property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873EA7AD6D71 FOREIGN KEY (inquiry_id) REFERENCES property_inquiry (id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873E549213EC FOREIGN KEY (property_id) REFERENCES properties (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873EA7AD6D71');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873E549213EC');
        $this->addSql('DROP TABLE offer');
    }
}
