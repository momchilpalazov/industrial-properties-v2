<?php

namespace App\Repository;

use App\Entity\PropertyCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PropertyCategory>
 *
 * @method PropertyCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method PropertyCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method PropertyCategory[]    findAll()
 * @method PropertyCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PropertyCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertyCategory::class);
    }

    public function save(PropertyCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PropertyCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Намира всички видими категории, сортирани по позиция
     */
    public function findAllVisible(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isVisible = :visible')
            ->setParameter('visible', true)
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Намира категория по slug
     */
    public function findOneBySlug(string $slug): ?PropertyCategory
    {
        return $this->createQueryBuilder('c')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 