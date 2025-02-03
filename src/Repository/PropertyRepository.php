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

    public function findFeatured(int $limit = 6): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->andWhere('p.isFeatured = :featured')
            ->setParameter('active', true)
            ->setParameter('featured', true)
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
            $qb->andWhere('p.price >= :minPrice')
               ->setParameter('minPrice', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $qb->andWhere('p.price <= :maxPrice')
               ->setParameter('maxPrice', $filters['max_price']);
        }

        if (!empty($filters['min_area'])) {
            $qb->andWhere('p.area >= :minArea')
               ->setParameter('minArea', $filters['min_area']);
        }

        if (!empty($filters['max_area'])) {
            $qb->andWhere('p.area <= :maxArea')
               ->setParameter('maxArea', $filters['max_area']);
        }

        if (!empty($filters['location'])) {
            $qb->andWhere('p.locationBg LIKE :location OR p.locationEn LIKE :location')
               ->setParameter('location', '%' . $filters['location'] . '%');
        }

        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $qb->orderBy('p.price', 'ASC');
                    break;
                case 'price_desc':
                    $qb->orderBy('p.price', 'DESC');
                    break;
                case 'area_asc':
                    $qb->orderBy('p.area', 'ASC');
                    break;
                case 'area_desc':
                    $qb->orderBy('p.area', 'DESC');
                    break;
                default:
                    $qb->orderBy('p.createdAt', 'DESC');
            }
        } else {
            $qb->orderBy('p.createdAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
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

    public function getTypeStats(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.type, COUNT(p.id) as count')
            ->where('p.isActive = :active')
            ->setParameter('active', true)
            ->groupBy('p.type')
            ->getQuery()
            ->getResult();
    }
} 