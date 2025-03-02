<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302112420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE social_posts (id INT AUTO_INCREMENT NOT NULL, property_id INT NOT NULL, platform VARCHAR(20) NOT NULL, post_id VARCHAR(255) DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C2CD0E68549213EC (property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE social_posts ADD CONSTRAINT FK_C2CD0E68549213EC FOREIGN KEY (property_id) REFERENCES properties (id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873EA7AD6D71 FOREIGN KEY (inquiry_id) REFERENCES property_inquiries (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE social_posts DROP FOREIGN KEY FK_C2CD0E68549213EC');
        $this->addSql('DROP TABLE social_posts');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873EA7AD6D71');
    }
}
