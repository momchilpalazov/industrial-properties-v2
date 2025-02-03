<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

class ContactRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
        $this->table = 'contact_messages';
    }

    public function saveMessage(array $data): int
    {
        $this->connection->insert($this->table, [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'ip_address' => $data['ip_address'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 'new'
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function getUnreadMessagesCount(): int
    {
        $qb = $this->createQueryBuilder()
            ->select('COUNT(*)')
            ->from($this->table)
            ->where('status = :status')
            ->setParameter('status', 'new');

        return (int) $qb->executeQuery()->fetchOne();
    }

    public function markAsRead(int $id): bool
    {
        return (bool) $this->connection->update(
            $this->table,
            ['status' => 'read'],
            ['id' => $id]
        );
    }

    public function getLatestMessages(int $limit = 10): array
    {
        $qb = $this->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->orderBy('created_at', 'DESC')
            ->setMaxResults($limit);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function searchMessages(array $criteria): array
    {
        $qb = $this->createQueryBuilder()
            ->select('*')
            ->from($this->table);

        if (!empty($criteria['status'])) {
            $qb->andWhere('status = :status')
               ->setParameter('status', $criteria['status']);
        }

        if (!empty($criteria['email'])) {
            $qb->andWhere('email LIKE :email')
               ->setParameter('email', '%' . $criteria['email'] . '%');
        }

        if (!empty($criteria['date_from'])) {
            $qb->andWhere('created_at >= :date_from')
               ->setParameter('date_from', $criteria['date_from']);
        }

        if (!empty($criteria['date_to'])) {
            $qb->andWhere('created_at <= :date_to')
               ->setParameter('date_to', $criteria['date_to']);
        }

        $qb->orderBy('created_at', 'DESC');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function deleteOldMessages(int $daysOld = 90): int
    {
        $date = new \DateTime("-{$daysOld} days");
        
        return $this->connection->executeStatement(
            'DELETE FROM ' . $this->table . ' WHERE created_at < ?',
            [$date->format('Y-m-d H:i:s')]
        );
    }
} 