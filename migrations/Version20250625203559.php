<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625203559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Добавяме новите колони
        $this->addSql('ALTER TABLE about_settings ADD team_bg JSON NOT NULL DEFAULT "[]"');
        $this->addSql('ALTER TABLE about_settings ADD team_en JSON NOT NULL DEFAULT "[]"');
        $this->addSql('ALTER TABLE about_settings ADD team_de JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE about_settings ADD team_ru JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE about_settings ADD team_common JSON NOT NULL DEFAULT "[]"');
        
        // Мигрираме съществуващите данни от team в team_bg и team_common
        $connection = $this->connection;
        $stmt = $connection->prepare('SELECT id, team FROM about_settings WHERE team IS NOT NULL');
        $result = $stmt->executeQuery();
        
        while ($row = $result->fetchAssociative()) {
            $teamData = json_decode($row['team'], true);
            if (!is_array($teamData)) continue;
            
            $teamBg = [];
            $teamCommon = [];
            
            foreach ($teamData as $key => $member) {
                if (isset($member['name']) && isset($member['position'])) {
                    $teamBg[$key] = [
                        'name' => $member['name'],
                        'position' => $member['position']
                    ];
                }
                
                if (isset($member['image'])) {
                    $teamCommon[$key] = ['image' => $member['image']];
                }
            }
            
            $updateStmt = $connection->prepare('
                UPDATE about_settings 
                SET 
                    team_bg = :team_bg,
                    team_en = "[]",
                    team_de = "[]",
                    team_ru = "[]", 
                    team_common = :team_common
                WHERE id = :id
            ');
            
            $updateStmt->executeStatement([
                'team_bg' => json_encode($teamBg),
                'team_common' => json_encode($teamCommon),
                'id' => $row['id']
            ]);
        }
        
        // Накрая премахваме старата колона
        $this->addSql('ALTER TABLE about_settings DROP team');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE about_settings ADD team JSON NOT NULL, DROP team_bg, DROP team_en, DROP team_de, DROP team_ru, DROP team_common');
    }
}
