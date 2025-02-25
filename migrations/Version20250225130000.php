<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250225130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Създаване на таблица за типове имоти и преместване на типовете от properties в нея';
    }

    public function up(Schema $schema): void
    {
        // Създаваме новата таблица
        $this->addSql('CREATE TABLE property_types (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Добавяме колоната type_id в таблицата properties
        $this->addSql('ALTER TABLE properties ADD type_id INT NULL');
        $this->addSql('ALTER TABLE properties ADD CONSTRAINT FK_87C331C7C54C8C93 FOREIGN KEY (type_id) REFERENCES property_types(id)');
        $this->addSql('CREATE INDEX IDX_87C331C7C54C8C93 ON properties (type_id)');

        // Вмъкваме съществуващите типове в новата таблица
        $this->addSql("INSERT INTO property_types (name) VALUES 
            ('Склад'),
            ('Производствено помещение'),
            ('Логистичен център'),
            ('Офис сграда'),
            ('Търговски площи'),
            ('Парцел')");

        // Обновяваме връзките между имотите и типовете
        $this->addSql("UPDATE properties p 
            INNER JOIN property_types pt ON 
            CASE p.type 
                WHEN 'warehouse' THEN pt.name = 'Склад'
                WHEN 'manufacturing' THEN pt.name = 'Производствено помещение'
                WHEN 'logistics' THEN pt.name = 'Логистичен център'
                WHEN 'office' THEN pt.name = 'Офис сграда'
                WHEN 'retail' THEN pt.name = 'Търговски площи'
                WHEN 'land' THEN pt.name = 'Парцел'
            END
            SET p.type_id = pt.id");

        // Премахваме старата колона type
        $this->addSql('ALTER TABLE properties DROP type');
    }

    public function down(Schema $schema): void
    {
        // Добавяме обратно колоната type
        $this->addSql('ALTER TABLE properties ADD type VARCHAR(50) DEFAULT NULL');

        // Обновяваме старата колона type със стойности от property_types
        $this->addSql("UPDATE properties p 
            INNER JOIN property_types pt ON p.type_id = pt.id 
            SET p.type = CASE pt.name 
                WHEN 'Склад' THEN 'warehouse'
                WHEN 'Производствено помещение' THEN 'manufacturing'
                WHEN 'Логистичен център' THEN 'logistics'
                WHEN 'Офис сграда' THEN 'office'
                WHEN 'Търговски площи' THEN 'retail'
                WHEN 'Парцел' THEN 'land'
            END");

        // Премахваме foreign key и колоната type_id
        $this->addSql('ALTER TABLE properties DROP FOREIGN KEY FK_87C331C7C54C8C93');
        $this->addSql('DROP INDEX IDX_87C331C7C54C8C93 ON properties');
        $this->addSql('ALTER TABLE properties DROP type_id');

        // Изтриваме таблицата property_types
        $this->addSql('DROP TABLE property_types');
    }
} 