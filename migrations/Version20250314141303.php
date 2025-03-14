<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250314141303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE property_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, name_en VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, description_en LONGTEXT DEFAULT NULL, position INT DEFAULT 0 NOT NULL, is_visible TINYINT(1) DEFAULT 1 NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8F52665B989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE properties ADD category_id INT DEFAULT NULL, CHANGE type_id type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE properties ADD CONSTRAINT FK_87C331C712469DE2 FOREIGN KEY (category_id) REFERENCES property_categories (id)');
        $this->addSql('CREATE INDEX IDX_87C331C712469DE2 ON properties (category_id)');
        $this->addSql('ALTER TABLE property_types ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE property_types ADD CONSTRAINT FK_9F23483D12469DE2 FOREIGN KEY (category_id) REFERENCES property_categories (id)');
        $this->addSql('CREATE INDEX IDX_9F23483D12469DE2 ON property_types (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE properties DROP FOREIGN KEY FK_87C331C712469DE2');
        $this->addSql('ALTER TABLE property_types DROP FOREIGN KEY FK_9F23483D12469DE2');
        $this->addSql('DROP TABLE property_categories');
        $this->addSql('DROP INDEX IDX_87C331C712469DE2 ON properties');
        $this->addSql('ALTER TABLE properties DROP category_id, CHANGE type_id type_id INT NOT NULL');
        $this->addSql('DROP INDEX IDX_9F23483D12469DE2 ON property_types');
        $this->addSql('ALTER TABLE property_types DROP category_id');
    }
}
