<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624123333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blog_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, name_en VARCHAR(255) DEFAULT NULL, name_de VARCHAR(255) DEFAULT NULL, name_ru VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, description_en LONGTEXT DEFAULT NULL, description_de LONGTEXT DEFAULT NULL, description_ru LONGTEXT DEFAULT NULL, slug VARCHAR(255) NOT NULL, is_visible TINYINT(1) DEFAULT 1 NOT NULL, position INT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_DC356481989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blog_posts ADD category_id INT DEFAULT NULL, ADD title_de VARCHAR(255) DEFAULT NULL, ADD title_ru VARCHAR(255) DEFAULT NULL, ADD content_de LONGTEXT DEFAULT NULL, ADD content_ru LONGTEXT DEFAULT NULL, ADD excerpt_de VARCHAR(500) DEFAULT NULL, ADD excerpt_ru VARCHAR(500) DEFAULT NULL, DROP category');
        $this->addSql('ALTER TABLE blog_posts ADD CONSTRAINT FK_78B2F93212469DE2 FOREIGN KEY (category_id) REFERENCES blog_categories (id)');
        $this->addSql('CREATE INDEX IDX_78B2F93212469DE2 ON blog_posts (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog_posts DROP FOREIGN KEY FK_78B2F93212469DE2');
        $this->addSql('DROP TABLE blog_categories');
        $this->addSql('DROP INDEX IDX_78B2F93212469DE2 ON blog_posts');
        $this->addSql('ALTER TABLE blog_posts ADD category VARCHAR(255) NOT NULL, DROP category_id, DROP title_de, DROP title_ru, DROP content_de, DROP content_ru, DROP excerpt_de, DROP excerpt_ru');
    }
}
