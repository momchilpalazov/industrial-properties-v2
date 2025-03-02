<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302101054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873EA7AD6D71');
        $this->addSql('CREATE TABLE property_inquiries (id INT AUTO_INCREMENT NOT NULL, property_id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, is_read TINYINT(1) NOT NULL, response LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7023E668549213EC (property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE property_inquiries ADD CONSTRAINT FK_7023E668549213EC FOREIGN KEY (property_id) REFERENCES properties (id)');
        $this->addSql('ALTER TABLE property_inquiry DROP FOREIGN KEY FK_3F44988549213EC');
        $this->addSql('DROP TABLE property_inquiry');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873EA7AD6D71');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873EA7AD6D71 FOREIGN KEY (inquiry_id) REFERENCES property_inquiries (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873EA7AD6D71');
        $this->addSql('CREATE TABLE property_inquiry (id INT AUTO_INCREMENT NOT NULL, property_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, message LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_read TINYINT(1) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', response LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_3F44988549213EC (property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE property_inquiry ADD CONSTRAINT FK_3F44988549213EC FOREIGN KEY (property_id) REFERENCES properties (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE property_inquiries DROP FOREIGN KEY FK_7023E668549213EC');
        $this->addSql('DROP TABLE property_inquiries');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873EA7AD6D71');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873EA7AD6D71 FOREIGN KEY (inquiry_id) REFERENCES property_inquiry (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
