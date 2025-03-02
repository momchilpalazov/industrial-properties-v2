<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302100208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE property_view (id INT AUTO_INCREMENT NOT NULL, property_id INT NOT NULL, ip_address VARCHAR(255) DEFAULT NULL, viewed_at DATETIME NOT NULL, user_agent VARCHAR(255) DEFAULT NULL, INDEX IDX_E1E514B4549213EC (property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE property_view ADD CONSTRAINT FK_E1E514B4549213EC FOREIGN KEY (property_id) REFERENCES properties (id)');
        $this->addSql('ALTER TABLE properties DROP views, CHANGE sold_at sold_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property_view DROP FOREIGN KEY FK_E1E514B4549213EC');
        $this->addSql('DROP TABLE property_view');
        $this->addSql('ALTER TABLE properties ADD views INT DEFAULT 0 NOT NULL, CHANGE sold_at sold_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
