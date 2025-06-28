<?php

namespace App\Repository;

use App\Entity\ContributorReward;
use App\Entity\ContributorProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for ContributorReward entity
 * Manages all database operations related to contributor rewards
 */
class ContributorRewardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContributorReward::class);
    }

    /**
     * Save contributor reward to database
     */
    public function save(ContributorReward $contributorReward, bool $flush = false): void
    {
        $this->getEntityManager()->persist($contributorReward);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove contributor reward from database
     */
    public function remove(ContributorReward $contributorReward, bool $flush = false): void
    {
        $this->getEntityManager()->remove($contributorReward);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find rewards by contributor
     */
    public function findByContributor(ContributorProfile $contributor): array
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.contributor = :contributor')
            ->setParameter('contributor', $contributor)
            ->orderBy('cr.earnedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find rewards by type
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.type = :type')
            ->setParameter('type', $type)
            ->orderBy('cr.earnedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find rewards by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.status = :status')
            ->setParameter('status', $status)
            ->orderBy('cr.earnedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find pending rewards for processing
     */
    public function findPendingRewards(): array
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('cr.earnedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calculate total points for contributor
     */
    public function getTotalPointsByContributor(ContributorProfile $contributor): int
    {
        $result = $this->createQueryBuilder('cr')
            ->select('SUM(cr.points)')
            ->where('cr.contributor = :contributor')
            ->andWhere('cr.status = :status')
            ->setParameter('contributor', $contributor)
            ->setParameter('status', 'awarded')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?: 0;
    }

    /**
     * Calculate total monetary rewards for contributor
     */
    public function getTotalMonetaryByContributor(ContributorProfile $contributor): float
    {
        $result = $this->createQueryBuilder('cr')
            ->select('SUM(cr.monetaryValue)')
            ->where('cr.contributor = :contributor')
            ->andWhere('cr.status = :status')
            ->andWhere('cr.monetaryValue IS NOT NULL')
            ->setParameter('contributor', $contributor)
            ->setParameter('status', 'awarded')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?: 0.0;
    }

    /**
     * Get reward statistics
     */
    public function getRewardStats(): array
    {
        $qb = $this->createQueryBuilder('cr');
        
        return [
            'total_rewards' => $qb->select('COUNT(cr.id)')
                ->getQuery()
                ->getSingleScalarResult(),
                
            'pending_rewards' => $qb->select('COUNT(cr.id)')
                ->resetDQLPart('select')
                ->where('cr.status = :status')
                ->setParameter('status', 'pending')
                ->getQuery()
                ->getSingleScalarResult(),
                
            'awarded_rewards' => $qb->select('COUNT(cr.id)')
                ->resetDQLPart('select')
                ->resetDQLPart('where')
                ->where('cr.status = :status')
                ->setParameter('status', 'awarded')
                ->getQuery()
                ->getSingleScalarResult(),
                
            'total_points_distributed' => $qb->select('SUM(cr.points)')
                ->resetDQLPart('select')
                ->resetDQLPart('where')
                ->where('cr.status = :status')
                ->setParameter('status', 'awarded')
                ->getQuery()
                ->getSingleScalarResult() ?: 0,
                
            'total_monetary_distributed' => $qb->select('SUM(cr.monetaryValue)')
                ->resetDQLPart('select')
                ->resetDQLPart('where')
                ->where('cr.status = :status')
                ->andWhere('cr.monetaryValue IS NOT NULL')
                ->setParameter('status', 'awarded')
                ->getQuery()
                ->getSingleScalarResult() ?: 0
        ];
    }

    /**
     * Find top earners by points
     */
    public function findTopEarners(int $limit = 10): array
    {
        return $this->createQueryBuilder('cr')
            ->select('cr.contributor, SUM(cr.points) as total_points')
            ->where('cr.status = :status')
            ->setParameter('status', 'awarded')
            ->groupBy('cr.contributor')
            ->orderBy('total_points', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get monthly reward statistics
     */
    public function getMonthlyStats(\DateTime $month): array
    {
        $startDate = clone $month;
        $startDate->modify('first day of this month')->setTime(0, 0, 0);
        
        $endDate = clone $month;
        $endDate->modify('last day of this month')->setTime(23, 59, 59);

        return $this->createQueryBuilder('cr')
            ->select('COUNT(cr.id) as rewards_count')
            ->addSelect('SUM(cr.points) as total_points')
            ->addSelect('SUM(cr.monetaryValue) as total_monetary')
            ->where('cr.earnedAt BETWEEN :start AND :end')
            ->andWhere('cr.status = :status')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('status', 'awarded')
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Find rewards by contributor and type
     */
    public function findByContributorAndType(ContributorProfile $contributor, string $type): array
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.contributor = :contributor')
            ->andWhere('cr.type = :type')
            ->setParameter('contributor', $contributor)
            ->setParameter('type', $type)
            ->orderBy('cr.earnedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if contributor has specific reward
     */
    public function hasReward(ContributorProfile $contributor, string $type, string $reason = null): bool
    {
        $qb = $this->createQueryBuilder('cr')
            ->where('cr.contributor = :contributor')
            ->andWhere('cr.type = :type')
            ->andWhere('cr.status = :status')
            ->setParameter('contributor', $contributor)
            ->setParameter('type', $type)
            ->setParameter('status', 'awarded');

        if ($reason) {
            $qb->andWhere('cr.reason = :reason')
               ->setParameter('reason', $reason);
        }

        return $qb->getQuery()->getOneOrNullResult() !== null;
    }

    /**
     * Get recent rewards for dashboard
     */
    public function getRecentRewards(int $limit = 20): array
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.status = :status')
            ->setParameter('status', 'awarded')
            ->orderBy('cr.earnedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
