<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250304142617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE property_pdf_files (
            id INT AUTO_INCREMENT NOT NULL,
            property_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            title VARCHAR(255) DEFAULT NULL,
            title_en VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_PROPERTY_PDF_FILES_PROPERTY (property_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE property_pdf_files ADD CONSTRAINT FK_PROPERTY_PDF_FILES_PROPERTY 
            FOREIGN KEY (property_id) REFERENCES properties (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property_pdf_files DROP FOREIGN KEY FK_PROPERTY_PDF_FILES_PROPERTY');
        $this->addSql('DROP TABLE property_pdf_files');
    }
}
