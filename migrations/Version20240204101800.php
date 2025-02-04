<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240204101800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the initial database schema';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE properties (
            id INT AUTO_INCREMENT NOT NULL,
            title_bg VARCHAR(255) NOT NULL,
            title_en VARCHAR(255) DEFAULT NULL,
            title_de VARCHAR(255) DEFAULT NULL,
            title_ru VARCHAR(255) DEFAULT NULL,
            description_bg TEXT DEFAULT NULL,
            description_en TEXT DEFAULT NULL,
            description_de TEXT DEFAULT NULL,
            description_ru TEXT DEFAULT NULL,
            price DECIMAL(10,2) DEFAULT NULL,
            area DECIMAL(10,2) DEFAULT NULL,
            type VARCHAR(50) NOT NULL,
            status VARCHAR(50) NOT NULL,
            location_bg VARCHAR(255) NOT NULL,
            location_en VARCHAR(255) DEFAULT NULL,
            location_de VARCHAR(255) DEFAULT NULL,
            location_ru VARCHAR(255) DEFAULT NULL,
            address VARCHAR(255) DEFAULT NULL,
            latitude DECIMAL(10,8) DEFAULT NULL,
            longitude DECIMAL(11,8) DEFAULT NULL,
            featured TINYINT(1) NOT NULL DEFAULT 0,
            pdf_flyer VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            updated_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE property_images (
            id INT AUTO_INCREMENT NOT NULL,
            property_id INT NOT NULL,
            path VARCHAR(255) NOT NULL,
            is_main TINYINT(1) NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            INDEX IDX_8E0C47D1549213EC (property_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE property_features (
            id INT AUTO_INCREMENT NOT NULL,
            property_id INT NOT NULL,
            feature_bg VARCHAR(255) NOT NULL,
            feature_en VARCHAR(255) DEFAULT NULL,
            feature_de VARCHAR(255) DEFAULT NULL,
            feature_ru VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            INDEX IDX_7A0A9B5A549213EC (property_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE property_pdf_files (
            id INT AUTO_INCREMENT NOT NULL,
            property_id INT NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            file_size INT NOT NULL,
            version INT NOT NULL DEFAULT 1,
            language VARCHAR(2) NOT NULL,
            created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            updated_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            INDEX IDX_D6D4CE62549213EC (property_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('CREATE TABLE inquiries (
            id INT AUTO_INCREMENT NOT NULL,
            property_id INT DEFAULT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            message TEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT "new",
            created_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            updated_at DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            INDEX IDX_5C9E2D37549213EC (property_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        $this->addSql('ALTER TABLE property_images ADD CONSTRAINT FK_8E0C47D1549213EC FOREIGN KEY (property_id) REFERENCES properties (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_features ADD CONSTRAINT FK_7A0A9B5A549213EC FOREIGN KEY (property_id) REFERENCES properties (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_pdf_files ADD CONSTRAINT FK_D6D4CE62549213EC FOREIGN KEY (property_id) REFERENCES properties (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inquiries ADD CONSTRAINT FK_5C9E2D37549213EC FOREIGN KEY (property_id) REFERENCES properties (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE property_images DROP FOREIGN KEY FK_8E0C47D1549213EC');
        $this->addSql('ALTER TABLE property_features DROP FOREIGN KEY FK_7A0A9B5A549213EC');
        $this->addSql('ALTER TABLE property_pdf_files DROP FOREIGN KEY FK_D6D4CE62549213EC');
        $this->addSql('ALTER TABLE inquiries DROP FOREIGN KEY FK_5C9E2D37549213EC');
        $this->addSql('DROP TABLE properties');
        $this->addSql('DROP TABLE property_images');
        $this->addSql('DROP TABLE property_features');
        $this->addSql('DROP TABLE property_pdf_files');
        $this->addSql('DROP TABLE inquiries');
    }
} 