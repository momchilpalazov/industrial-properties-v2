<?php

declare(strict_types=1);

namespace App\Repository;

interface RepositoryInterface
{
    public function find(int $id): ?array;
    public function findAll(): array;
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
    public function findOneBy(array $criteria): ?array;
    public function count(array $criteria = []): int;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
} 