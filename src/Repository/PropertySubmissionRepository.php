<?php

namespace App\Repository;

use App\Entity\PropertySubmission;
use App\Entity\ContributorProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * Repository for PropertySubmission entity
 * Manages all database operations related to property submissions
 */
class PropertySubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertySubmission::class);
    }

    /**
     * Save property submission to database
     */
    public function save(PropertySubmission $propertySubmission, bool $flush = false): void
    {
        $this->getEntityManager()->persist($propertySubmission);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove property submission from database
     */
    public function remove(PropertySubmission $propertySubmission, bool $flush = false): void
    {
        $this->getEntityManager()->remove($propertySubmission);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find submissions by status
     */
    public function findByStatus(string $status, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('ps')
            ->where('ps.status = :status')
            ->setParameter('status', $status)
            ->orderBy('ps.submittedAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find pending submissions for review
     */
    public function findPendingReview(): array
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.status = :status')
            ->setParameter('status', 'pending_review')
            ->orderBy('ps.submittedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find submissions by contributor
     */
    public function findByContributor(ContributorProfile $contributor): array
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.contributor = :contributor')
            ->setParameter('contributor', $contributor)
            ->orderBy('ps.submittedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find submissions by country
     */
    public function findByCountry(string $country): array
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.country = :country')
            ->setParameter('country', $country)
            ->orderBy('ps.submittedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find approved submissions for property creation
     */
    public function findApprovedForCreation(): array
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.status = :status')
            ->andWhere('ps.propertyCreated = :created')
            ->setParameter('status', 'approved')
            ->setParameter('created', false)
            ->orderBy('ps.submittedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search submissions by various criteria
     */
    public function searchSubmissions(array $criteria): array
    {
        $qb = $this->createQueryBuilder('ps');

        if (!empty($criteria['status'])) {
            $qb->andWhere('ps.status = :status')
               ->setParameter('status', $criteria['status']);
        }

        if (!empty($criteria['country'])) {
            $qb->andWhere('ps.country = :country')
               ->setParameter('country', $criteria['country']);
        }

        if (!empty($criteria['property_type'])) {
            $qb->andWhere('ps.propertyType = :property_type')
               ->setParameter('property_type', $criteria['property_type']);
        }

        if (!empty($criteria['contributor_id'])) {
            $qb->andWhere('ps.contributor = :contributor_id')
               ->setParameter('contributor_id', $criteria['contributor_id']);
        }

        if (!empty($criteria['min_area'])) {
            $qb->andWhere('ps.area >= :min_area')
               ->setParameter('min_area', $criteria['min_area']);
        }

        if (!empty($criteria['max_area'])) {
            $qb->andWhere('ps.area <= :max_area')
               ->setParameter('max_area', $criteria['max_area']);
        }

        if (!empty($criteria['date_from'])) {
            $qb->andWhere('ps.submittedAt >= :date_from')
               ->setParameter('date_from', $criteria['date_from']);
        }

        if (!empty($criteria['date_to'])) {
            $qb->andWhere('ps.submittedAt <= :date_to')
               ->setParameter('date_to', $criteria['date_to']);
        }

        return $qb->orderBy('ps.submittedAt', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Get submission statistics
     */
    public function getSubmissionStats(): array
    {
        $qb = $this->createQueryBuilder('ps');
        
        return [
            'total_submissions' => $qb->select('COUNT(ps.id)')
                ->getQuery()
                ->getSingleScalarResult(),
                
            'pending_submissions' => $qb->select('COUNT(ps.id)')
                ->resetDQLPart('select')
                ->where('ps.status = :status')
                ->setParameter('status', 'pending_review')
                ->getQuery()
                ->getSingleScalarResult(),
                
            'approved_submissions' => $qb->select('COUNT(ps.id)')
                ->resetDQLPart('select')
                ->resetDQLPart('where')
                ->where('ps.status = :status')
                ->setParameter('status', 'approved')
                ->getQuery()
                ->getSingleScalarResult(),
                
            'rejected_submissions' => $qb->select('COUNT(ps.id)')
                ->resetDQLPart('select')
                ->resetDQLPart('where')
                ->where('ps.status = :status')
                ->setParameter('status', 'rejected')
                ->getQuery()
                ->getSingleScalarResult(),
                
            'countries_covered' => $qb->select('COUNT(DISTINCT ps.country)')
                ->resetDQLPart('select')
                ->resetDQLPart('where')
                ->getQuery()
                ->getSingleScalarResult()
        ];
    }

    /**
     * Get monthly submission statistics
     */
    public function getMonthlyStats(\DateTime $month): array
    {
        $startDate = clone $month;
        $startDate->modify('first day of this month')->setTime(0, 0, 0);
        
        $endDate = clone $month;
        $endDate->modify('last day of this month')->setTime(23, 59, 59);

        return $this->createQueryBuilder('ps')
            ->select('COUNT(ps.id) as submissions_count')
            ->addSelect('ps.status')
            ->where('ps.submittedAt BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->groupBy('ps.status')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find submissions requiring AI analysis
     */
    public function findForAiAnalysis(): array
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.aiAnalysisCompleted = :completed')
            ->andWhere('ps.status = :status')
            ->setParameter('completed', false)
            ->setParameter('status', 'pending_review')
            ->orderBy('ps.submittedAt', 'ASC')
            ->setMaxResults(10) // Process in batches
            ->getQuery()
            ->getResult();
    }

    /**
     * Find high-quality submissions for featured listing
     */
    public function findFeaturedSubmissions(int $limit = 6): array
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.status = :status')
            ->andWhere('ps.qualityScore >= :min_score')
            ->setParameter('status', 'approved')
            ->setParameter('min_score', 85)
            ->orderBy('ps.qualityScore', 'DESC')
            ->addOrderBy('ps.submittedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get European coverage statistics
     */
    public function getEuropeanCoverage(): array
    {
        return $this->createQueryBuilder('ps')
            ->select('ps.country, COUNT(ps.id) as submission_count')
            ->where('ps.status IN (:statuses)')
            ->setParameter('statuses', ['approved', 'pending_review'])
            ->groupBy('ps.country')
            ->orderBy('submission_count', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
