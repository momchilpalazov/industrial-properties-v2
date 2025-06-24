<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624190818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add multilingual support to FAQ: make question_de, question_ru, answer_de, answer_ru NOT NULL and allow category_id to be NULL';
    }

    public function up(Schema $schema): void
    {
        // First, update NULL values with default content based on Bulgarian text
        $this->addSql("UPDATE faqs SET question_de = question_bg WHERE question_de IS NULL OR question_de = ''");
        $this->addSql("UPDATE faqs SET question_ru = question_bg WHERE question_ru IS NULL OR question_ru = ''");
        $this->addSql("UPDATE faqs SET answer_de = answer_bg WHERE answer_de IS NULL OR answer_de = ''");
        $this->addSql("UPDATE faqs SET answer_ru = answer_bg WHERE answer_ru IS NULL OR answer_ru = ''");
        
        // Then change the column constraints
        $this->addSql('ALTER TABLE faqs CHANGE category_id category_id INT DEFAULT NULL, CHANGE question_de question_de VARCHAR(255) NOT NULL, CHANGE question_ru question_ru VARCHAR(255) NOT NULL, CHANGE answer_de answer_de LONGTEXT NOT NULL, CHANGE answer_ru answer_ru LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE faqs CHANGE category_id category_id INT NOT NULL, CHANGE question_de question_de VARCHAR(255) DEFAULT NULL, CHANGE question_ru question_ru VARCHAR(255) DEFAULT NULL, CHANGE answer_de answer_de LONGTEXT DEFAULT NULL, CHANGE answer_ru answer_ru LONGTEXT DEFAULT NULL');
    }
}
