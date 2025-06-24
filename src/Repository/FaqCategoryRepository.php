<?php

namespace App\Repository;

use App\Entity\FaqCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FaqCategory>
 *
 * @method FaqCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaqCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaqCategory[]    findAll()
 * @method FaqCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqCategory::class);
    }

    public function save(FaqCategory $category, bool $flush = true): void
    {
        $this->_em->persist($category);
        
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(FaqCategory $category): void
    {
        $this->_em->remove($category);
        $this->_em->flush();
    }

    /**
     * Намери всички видими категории, подредени по позиция
     */
    public function findVisible(): array
    {
        return $this->createQueryBuilder('fc')
            ->where('fc.isVisible = :visible')
            ->setParameter('visible', true)
            ->orderBy('fc.position', 'ASC')
            ->addOrderBy('fc.nameBg', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Намери всички категории, подредени по позиция
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('fc')
            ->orderBy('fc.position', 'ASC')
            ->addOrderBy('fc.nameBg', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Намери категория по slug
     */
    public function findBySlug(string $slug): ?FaqCategory
    {
        return $this->createQueryBuilder('fc')
            ->where('fc.slug = :slug')
            ->andWhere('fc.isVisible = :visible')
            ->setParameter('slug', $slug)
            ->setParameter('visible', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Намери следващата позиция за нова категория
     */
    public function getNextPosition(): int
    {
        $result = $this->createQueryBuilder('fc')
            ->select('MAX(fc.position)')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? $result + 1 : 0;
    }

    /**
     * Пренареди позициите на категориите
     */
    public function reorderPositions(array $positions): void
    {
        foreach ($positions as $id => $position) {
            $this->createQueryBuilder('fc')
                ->update()
                ->set('fc.position', ':position')
                ->where('fc.id = :id')
                ->setParameter('position', $position)
                ->setParameter('id', $id)
                ->getQuery()
                ->execute();
        }
    }

    /**
     * Намери категории с брой активни FAQ въпроси
     */
    public function findWithFaqCount(): array
    {
        return $this->createQueryBuilder('fc')
            ->leftJoin('fc.faqs', 'f')
            ->addSelect('COUNT(f.id) as faqCount')
            ->where('fc.isVisible = :visible')
            ->setParameter('visible', true)
            ->groupBy('fc.id')
            ->orderBy('fc.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Намери категории за админ панела с брой въпроси
     */
    public function findForAdmin(): array
    {
        return $this->createQueryBuilder('fc')
            ->leftJoin('fc.faqs', 'f', 'WITH', 'f.isActive = :active')
            ->addSelect('COUNT(f.id) as activeFaqCount')
            ->setParameter('active', true)
            ->groupBy('fc.id')
            ->orderBy('fc.position', 'ASC')
            ->addOrderBy('fc.nameBg', 'ASC')
            ->getQuery()
            ->getResult();
    }
}