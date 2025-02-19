<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219093947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog_posts ADD language VARCHAR(2) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE published_at published_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE is_published is_published TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE blog_posts RENAME INDEX slug TO UNIQ_78B2F932989D9B62');
        $this->addSql('ALTER TABLE properties CHANGE reference_number reference_number VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE properties RENAME INDEX reference_number TO UNIQ_87C331C78BF1AE50');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blog_posts DROP language, CHANGE created_at created_at DATETIME NOT NULL, CHANGE published_at published_at DATETIME NOT NULL, CHANGE is_published is_published TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE blog_posts RENAME INDEX uniq_78b2f932989d9b62 TO slug');
        $this->addSql('ALTER TABLE properties CHANGE reference_number reference_number VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE properties RENAME INDEX uniq_87c331c78bf1ae50 TO reference_number');
    }
}
