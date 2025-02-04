<?php

namespace App\Repository;

use App\Entity\Property;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Property>
 */
class PropertyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Property::class);
    }

    public function save(Property $property, bool $flush = false): void
    {
        $this->getEntityManager()->persist($property);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Property $property, bool $flush = false): void
    {
        $this->getEntityManager()->remove($property);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Намира избраните имоти
     */
    public function findFeatured(int $limit = 6): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isFeatured = :featured')
            ->andWhere('p.isActive = :active')
            ->setParameter('featured', true)
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findSimilar(Property $property, int $limit = 3): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->andWhere('p.id != :id')
            ->andWhere('p.type = :type')
            ->andWhere('p.price BETWEEN :minPrice AND :maxPrice')
            ->setParameter('active', true)
            ->setParameter('id', $property->getId())
            ->setParameter('type', $property->getType())
            ->setParameter('minPrice', $property->getPrice() * 0.7)
            ->setParameter('maxPrice', $property->getPrice() * 1.3)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Намира имоти по зададени критерии
     */
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->setParameter('active', true);

        if (!empty($filters['type'])) {
            $qb->andWhere('p.type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (!empty($filters['min_price'])) {
            $qb->andWhere('p.price >= :min_price')
               ->setParameter('min_price', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $qb->andWhere('p.price <= :max_price')
               ->setParameter('max_price', $filters['max_price']);
        }

        if (!empty($filters['min_area'])) {
            $qb->andWhere('p.area >= :min_area')
               ->setParameter('min_area', $filters['min_area']);
        }

        if (!empty($filters['max_area'])) {
            $qb->andWhere('p.area <= :max_area')
               ->setParameter('max_area', $filters['max_area']);
        }

        if (!empty($filters['location'])) {
            $qb->andWhere('p.location LIKE :location')
               ->setParameter('location', '%' . $filters['location'] . '%');
        }

        return $qb->orderBy('p.createdAt', 'DESC')
                 ->getQuery()
                 ->getResult();
    }

    public function getStats(): array
    {
        $total = $this->count([]);
        $active = $this->count(['isActive' => true]);
        $featured = $this->count(['isActive' => true, 'isFeatured' => true]);

        return [
            'total' => $total,
            'active' => $active,
            'featured' => $featured
        ];
    }

    /**
     * Връща статистика за броя имоти по тип
     */
    public function getPropertyTypeStats(): array
    {
        $stats = $this->createQueryBuilder('p')
            ->select('p.type, COUNT(p.id) as count')
            ->where('p.isActive = :active')
            ->setParameter('active', true)
            ->groupBy('p.type')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($stats as $stat) {
            $result[$stat['type']] = $stat['count'];
        }

        return $result;
    }

    public function searchByQuery(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.title LIKE :query')
            ->orWhere('p.titleEn LIKE :query')
            ->orWhere('p.location LIKE :query')
            ->orWhere('p.type LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * Връща списък с всички типове имоти
     */
    public function getPropertyTypes(): array
    {
        return [
            'industrial_land' => 'property.types.industrial_land',
            'industrial_building' => 'property.types.industrial_building',
            'logistics_center' => 'property.types.logistics_center',
            'warehouse' => 'property.types.warehouse',
            'production_facility' => 'property.types.production_facility'
        ];
    }
} 