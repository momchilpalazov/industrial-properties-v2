<?php

namespace App\Repository;

use App\Entity\BlogCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BlogCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogCategory::class);
    }

    public function save(BlogCategory $blogCategory, bool $flush = true): void
    {
        $this->_em->persist($blogCategory);
        
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(BlogCategory $blogCategory, bool $flush = true): void
    {
        $this->_em->remove($blogCategory);
        
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Намира всички видими категории, сортирани по позиция
     */
    public function findVisible(): array
    {
        return $this->createQueryBuilder('bc')
            ->where('bc.isVisible = :visible')
            ->setParameter('visible', true)
            ->orderBy('bc.position', 'ASC')
            ->addOrderBy('bc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Намира категория по slug
     */
    public function findBySlug(string $slug): ?BlogCategory
    {
        return $this->createQueryBuilder('bc')
            ->where('bc.slug = :slug')
            ->andWhere('bc.isVisible = :visible')
            ->setParameter('slug', $slug)
            ->setParameter('visible', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Намира следващата свободна позиция
     */
    public function getNextPosition(): int
    {
        $result = $this->createQueryBuilder('bc')
            ->select('MAX(bc.position)')
            ->getQuery()
            ->getSingleScalarResult();

        return ($result ?? 0) + 1;
    }
}
