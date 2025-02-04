<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250204102011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact_messages (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, phone VARCHAR(50) DEFAULT NULL, subject VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inquiries DROP FOREIGN KEY FK_5C9E2D37549213EC');
        $this->addSql('ALTER TABLE inquiries CHANGE message message LONGTEXT NOT NULL, CHANGE status status VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE inquiries ADD CONSTRAINT FK_1CCE4D5549213EC FOREIGN KEY (property_id) REFERENCES properties (id)');
        $this->addSql('ALTER TABLE inquiries RENAME INDEX idx_5c9e2d37549213ec TO IDX_1CCE4D5549213EC');
        $this->addSql('ALTER TABLE properties ADD is_active TINYINT(1) NOT NULL, ADD is_featured TINYINT(1) NOT NULL, ADD slug VARCHAR(255) NOT NULL, DROP status, DROP address, DROP latitude, DROP longitude, DROP featured, DROP pdf_flyer, CHANGE description_bg description_bg LONGTEXT DEFAULT NULL, CHANGE description_en description_en LONGTEXT DEFAULT NULL, CHANGE description_de description_de LONGTEXT DEFAULT NULL, CHANGE description_ru description_ru LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_87C331C7989D9B62 ON properties (slug)');
        $this->addSql('ALTER TABLE property_features DROP FOREIGN KEY FK_7A0A9B5A549213EC');
        $this->addSql('ALTER TABLE property_features ADD CONSTRAINT FK_E147E857549213EC FOREIGN KEY (property_id) REFERENCES properties (id)');
        $this->addSql('ALTER TABLE property_features RENAME INDEX idx_7a0a9b5a549213ec TO IDX_E147E857549213EC');
        $this->addSql('ALTER TABLE property_images DROP FOREIGN KEY FK_8E0C47D1549213EC');
        $this->addSql('ALTER TABLE property_images CHANGE is_main is_main TINYINT(1) NOT NULL, CHANGE sort_order sort_order INT NOT NULL');
        $this->addSql('ALTER TABLE property_images ADD CONSTRAINT FK_9E68D116549213EC FOREIGN KEY (property_id) REFERENCES properties (id)');
        $this->addSql('ALTER TABLE property_images RENAME INDEX idx_8e0c47d1549213ec TO IDX_9E68D116549213EC');
        $this->addSql('ALTER TABLE property_pdf_files DROP FOREIGN KEY FK_D6D4CE62549213EC');
        $this->addSql('ALTER TABLE property_pdf_files CHANGE version version INT NOT NULL');
        $this->addSql('ALTER TABLE property_pdf_files ADD CONSTRAINT FK_E64D0F3E549213EC FOREIGN KEY (property_id) REFERENCES properties (id)');
        $this->addSql('ALTER TABLE property_pdf_files RENAME INDEX idx_d6d4ce62549213ec TO IDX_E64D0F3E549213EC');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE contact_messages');
        $this->addSql('DROP INDEX UNIQ_87C331C7989D9B62 ON properties');
        $this->addSql('ALTER TABLE properties ADD status VARCHAR(50) NOT NULL, ADD address VARCHAR(255) DEFAULT NULL, ADD latitude NUMERIC(10, 8) DEFAULT NULL, ADD longitude NUMERIC(11, 8) DEFAULT NULL, ADD featured TINYINT(1) DEFAULT 0 NOT NULL, ADD pdf_flyer VARCHAR(255) DEFAULT NULL, DROP is_active, DROP is_featured, DROP slug, CHANGE description_bg description_bg TEXT DEFAULT NULL, CHANGE description_en description_en TEXT DEFAULT NULL, CHANGE description_de description_de TEXT DEFAULT NULL, CHANGE description_ru description_ru TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE inquiries DROP FOREIGN KEY FK_1CCE4D5549213EC');
        $this->addSql('ALTER TABLE inquiries CHANGE message message TEXT NOT NULL, CHANGE status status VARCHAR(20) DEFAULT \'new\' NOT NULL');
        $this->addSql('ALTER TABLE inquiries ADD CONSTRAINT FK_5C9E2D37549213EC FOREIGN KEY (property_id) REFERENCES properties (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE inquiries RENAME INDEX idx_1cce4d5549213ec TO IDX_5C9E2D37549213EC');
        $this->addSql('ALTER TABLE property_pdf_files DROP FOREIGN KEY FK_E64D0F3E549213EC');
        $this->addSql('ALTER TABLE property_pdf_files CHANGE version version INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE property_pdf_files ADD CONSTRAINT FK_D6D4CE62549213EC FOREIGN KEY (property_id) REFERENCES properties (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_pdf_files RENAME INDEX idx_e64d0f3e549213ec TO IDX_D6D4CE62549213EC');
        $this->addSql('ALTER TABLE property_features DROP FOREIGN KEY FK_E147E857549213EC');
        $this->addSql('ALTER TABLE property_features ADD CONSTRAINT FK_7A0A9B5A549213EC FOREIGN KEY (property_id) REFERENCES properties (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_features RENAME INDEX idx_e147e857549213ec TO IDX_7A0A9B5A549213EC');
        $this->addSql('ALTER TABLE property_images DROP FOREIGN KEY FK_9E68D116549213EC');
        $this->addSql('ALTER TABLE property_images CHANGE is_main is_main TINYINT(1) DEFAULT 0 NOT NULL, CHANGE sort_order sort_order INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE property_images ADD CONSTRAINT FK_8E0C47D1549213EC FOREIGN KEY (property_id) REFERENCES properties (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_images RENAME INDEX idx_9e68d116549213ec TO IDX_8E0C47D1549213EC');
    }
}
