<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractRepository implements RepositoryInterface
{
    protected Connection $connection;
    protected string $table;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find(int $id): ?array
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findAll(): array
    {
        return $this->findBy([]);
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->createQueryBuilder()
            ->select('*')
            ->from($this->table);

        $this->addCriteria($qb, $criteria);
        $this->addOrderBy($qb, $orderBy);

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function findOneBy(array $criteria): ?array
    {
        $result = $this->findBy($criteria, null, 1);
        return !empty($result) ? $result[0] : null;
    }

    public function count(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder()
            ->select('COUNT(*)')
            ->from($this->table);

        $this->addCriteria($qb, $criteria);

        return (int) $qb->executeQuery()->fetchOne();
    }

    protected function createQueryBuilder(): QueryBuilder
    {
        return $this->connection->createQueryBuilder();
    }

    protected function addCriteria(QueryBuilder $qb, array $criteria): void
    {
        foreach ($criteria as $field => $value) {
            $paramName = 'param_' . $field;
            $qb->andWhere("$field = :$paramName")
               ->setParameter($paramName, $value);
        }
    }

    protected function addOrderBy(QueryBuilder $qb, ?array $orderBy): void
    {
        if ($orderBy) {
            foreach ($orderBy as $field => $direction) {
                $qb->addOrderBy($field, $direction);
            }
        }
    }
} 