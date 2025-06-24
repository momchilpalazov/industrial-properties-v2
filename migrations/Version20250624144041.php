<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624144041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE faq_category (id INT AUTO_INCREMENT NOT NULL, name_bg VARCHAR(255) NOT NULL, name_en VARCHAR(255) NOT NULL, name_de VARCHAR(255) NOT NULL, name_ru VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_FAEEE0D6989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE faqs ADD category_id INT NOT NULL, ADD question_de VARCHAR(255) NOT NULL, ADD question_ru VARCHAR(255) NOT NULL, ADD answer_de LONGTEXT NOT NULL, ADD answer_ru LONGTEXT NOT NULL, DROP category');
        $this->addSql('ALTER TABLE faqs ADD CONSTRAINT FK_8934BEE512469DE2 FOREIGN KEY (category_id) REFERENCES faq_category (id)');
        $this->addSql('CREATE INDEX IDX_8934BEE512469DE2 ON faqs (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE faqs DROP FOREIGN KEY FK_8934BEE512469DE2');
        $this->addSql('DROP TABLE faq_category');
        $this->addSql('DROP INDEX IDX_8934BEE512469DE2 ON faqs');
        $this->addSql('ALTER TABLE faqs ADD category VARCHAR(50) NOT NULL, DROP category_id, DROP question_de, DROP question_ru, DROP answer_de, DROP answer_ru');
    }
}
