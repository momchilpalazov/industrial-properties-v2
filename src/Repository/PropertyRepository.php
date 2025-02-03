<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

class PropertyRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
        $this->table = 'properties';
    }

    public function getFeaturedProperties(int $limit = 6): array
    {
        $qb = $this->createQueryBuilder()
            ->select('p.*, pi.image_path')
            ->from($this->table, 'p')
            ->leftJoin(
                'p',
                'property_images',
                'pi',
                'p.id = pi.property_id AND pi.is_main = 1'
            )
            ->where('p.featured = 1')
            ->orderBy('p.created_at', 'DESC')
            ->setMaxResults($limit);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function getLatestProperties(int $limit = 3): array
    {
        $qb = $this->createQueryBuilder()
            ->select('p.*, pi.image_path')
            ->from($this->table, 'p')
            ->leftJoin(
                'p',
                'property_images',
                'pi',
                'p.id = pi.property_id AND pi.is_main = 1'
            )
            ->orderBy('p.created_at', 'DESC')
            ->setMaxResults($limit);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function searchProperties(array $criteria): array
    {
        $qb = $this->createQueryBuilder()
            ->select('p.*, pi.image_path')
            ->from($this->table, 'p')
            ->leftJoin(
                'p',
                'property_images',
                'pi',
                'p.id = pi.property_id AND pi.is_main = 1'
            );

        if (!empty($criteria['type'])) {
            $qb->andWhere('p.type = :type')
               ->setParameter('type', $criteria['type']);
        }

        if (!empty($criteria['min_area'])) {
            $qb->andWhere('p.area >= :min_area')
               ->setParameter('min_area', $criteria['min_area']);
        }

        if (!empty($criteria['max_area'])) {
            $qb->andWhere('p.area <= :max_area')
               ->setParameter('max_area', $criteria['max_area']);
        }

        if (!empty($criteria['min_price'])) {
            $qb->andWhere('p.price >= :min_price')
               ->setParameter('min_price', $criteria['min_price']);
        }

        if (!empty($criteria['max_price'])) {
            $qb->andWhere('p.price <= :max_price')
               ->setParameter('max_price', $criteria['max_price']);
        }

        $qb->orderBy('p.created_at', 'DESC');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function create(array $data): int
    {
        $this->connection->insert($this->table, $data);
        return (int) $this->connection->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        return (bool) $this->connection->update(
            $this->table,
            $data,
            ['id' => $id]
        );
    }

    public function delete(int $id): bool
    {
        return (bool) $this->connection->delete($this->table, ['id' => $id]);
    }
} 