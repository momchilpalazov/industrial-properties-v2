<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Миграция на данните от team към многоезичните полета
 */
final class Version20250625204100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Миграция на съществуващите данни за екипа от team към team_bg и team_common';
    }

    public function up(Schema $schema): void
    {
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
        // Възстановяваме старата team колона
        $this->addSql('ALTER TABLE about_settings ADD team JSON NOT NULL');
        
        // Мигрираме данните обратно
        $connection = $this->connection;
        $stmt = $connection->prepare('SELECT id, team_bg, team_common FROM about_settings');
        $result = $stmt->executeQuery();
        
        while ($row = $result->fetchAssociative()) {
            $teamBg = json_decode($row['team_bg'], true) ?: [];
            $teamCommon = json_decode($row['team_common'], true) ?: [];
            
            $team = [];
            foreach ($teamBg as $key => $member) {
                $team[$key] = [
                    'name' => $member['name'] ?? '',
                    'position' => $member['position'] ?? ''
                ];
                
                if (isset($teamCommon[$key]['image'])) {
                    $team[$key]['image'] = $teamCommon[$key]['image'];
                }
            }
            
            $updateStmt = $connection->prepare('UPDATE about_settings SET team = :team WHERE id = :id');
            $updateStmt->executeStatement([
                'team' => json_encode($team),
                'id' => $row['id']
            ]);
        }
    }
}
