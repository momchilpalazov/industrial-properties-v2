<?php

namespace App\Repository;

use App\Entity\ContributorProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * Repository for ContributorProfile entity
 * Manages all database operations related to contributor profiles
 */
class ContributorProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContributorProfile::class);
    }

    /**
     * Save contributor profile to database
     */
    public function save(ContributorProfile $contributorProfile, bool $flush = false): void
    {
        $this->getEntityManager()->persist($contributorProfile);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove contributor profile from database
     */
    public function remove(ContributorProfile $contributorProfile, bool $flush = false): void
    {
        $this->getEntityManager()->remove($contributorProfile);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find contributor by European Contributor ID
     */
    public function findByEuropeanId(string $europeanId): ?ContributorProfile
    {
        return $this->findOneBy(['europeanContributorId' => $europeanId]);
    }

    /**
     * Find top contributors by points (European leaderboard)
     */
    public function findTopContributors(int $limit = 50): array
    {
        return $this->createQueryBuilder('cp')
            ->where('cp.status = :status')
            ->setParameter('status', 'active')
            ->orderBy('cp.totalPoints', 'DESC')
            ->addOrderBy('cp.level', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find top contributors by country
     */
    public function findTopContributorsByCountry(string $country, int $limit = 20): array
    {
        return $this->createQueryBuilder('cp')
            ->where('cp.country = :country')
            ->andWhere('cp.status = :status')
            ->setParameter('country', $country)
            ->setParameter('status', 'active')
            ->orderBy('cp.totalPoints', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find contributors by expertise area
     */
    public function findByExpertise(string $expertise): array
    {
        return $this->createQueryBuilder('cp')
            ->where('cp.expertise LIKE :expertise')
            ->andWhere('cp.status = :status')
            ->setParameter('expertise', '%' . $expertise . '%')
            ->setParameter('status', 'active')
            ->orderBy('cp.totalPoints', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get statistics for European contributors
     */
    public function getEuropeanStats(): array
    {
        $qb = $this->createQueryBuilder('cp');
        
        return [
            'total_contributors' => $qb->select('COUNT(cp.id)')
                ->where('cp.status = :status')
                ->setParameter('status', 'active')
                ->getQuery()
                ->getSingleScalarResult(),
                
            'countries_count' => $qb->select('COUNT(DISTINCT cp.country)')
                ->resetDQLPart('select')
                ->where('cp.status = :status')
                ->setParameter('status', 'active')
                ->getQuery()
                ->getSingleScalarResult(),
                
            'total_submissions' => $qb->select('SUM(cp.approvedSubmissions)')
                ->resetDQLPart('select')
                ->where('cp.status = :status')
                ->setParameter('status', 'active')
                ->getQuery()
                ->getSingleScalarResult() ?: 0,
                
            'total_points' => $qb->select('SUM(cp.totalPoints)')
                ->resetDQLPart('select')
                ->where('cp.status = :status')
                ->setParameter('status', 'active')
                ->getQuery()
                ->getSingleScalarResult() ?: 0
        ];
    }

    /**
     * Find contributors by level
     */
    public function findByLevel(string $level): array
    {
        return $this->createQueryBuilder('cp')
            ->where('cp.level = :level')
            ->andWhere('cp.status = :status')
            ->setParameter('level', $level)
            ->setParameter('status', 'active')
            ->orderBy('cp.totalPoints', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search contributors by various criteria
     */
    public function searchContributors(array $criteria): array
    {
        $qb = $this->createQueryBuilder('cp')
            ->where('cp.status = :status')
            ->setParameter('status', 'active');

        if (!empty($criteria['country'])) {
            $qb->andWhere('cp.country = :country')
               ->setParameter('country', $criteria['country']);
        }

        if (!empty($criteria['level'])) {
            $qb->andWhere('cp.level = :level')
               ->setParameter('level', $criteria['level']);
        }

        if (!empty($criteria['expertise'])) {
            $qb->andWhere('cp.expertise LIKE :expertise')
               ->setParameter('expertise', '%' . $criteria['expertise'] . '%');
        }

        if (!empty($criteria['min_points'])) {
            $qb->andWhere('cp.totalPoints >= :min_points')
               ->setParameter('min_points', $criteria['min_points']);
        }

        return $qb->orderBy('cp.totalPoints', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Get monthly statistics
     */
    public function getMonthlyStats(\DateTime $month): array
    {
        $startDate = clone $month;
        $startDate->modify('first day of this month')->setTime(0, 0, 0);
        
        $endDate = clone $month;
        $endDate->modify('last day of this month')->setTime(23, 59, 59);

        return $this->createQueryBuilder('cp')
            ->select('COUNT(cp.id) as new_contributors')
            ->where('cp.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getSingleResult();
    }
}
