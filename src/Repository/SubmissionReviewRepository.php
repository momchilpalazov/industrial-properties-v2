<?php

namespace App\Repository;

use App\Entity\SubmissionReview;
use App\Entity\PropertySubmission;
use App\Entity\ContributorProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubmissionReview>
 */
class SubmissionReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubmissionReview::class);
    }

    /**
     * Find all reviews for a specific submission
     */
    public function findBySubmission(PropertySubmission $submission): array
    {
        return $this->createQueryBuilder('sr')
            ->where('sr.submission = :submission')
            ->setParameter('submission', $submission)
            ->orderBy('sr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reviews by a specific reviewer
     */
    public function findByReviewer(ContributorProfile $reviewer): array
    {
        return $this->createQueryBuilder('sr')
            ->where('sr.reviewer = :reviewer')
            ->setParameter('reviewer', $reviewer)
            ->orderBy('sr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find pending reviews (community reviews that need admin approval)
     */
    public function findPendingAdminReviews(): array
    {
        return $this->createQueryBuilder('sr')
            ->where('sr.reviewType = :communityType')
            ->andWhere('sr.status = :pendingStatus')
            ->setParameter('communityType', 'community')
            ->setParameter('pendingStatus', 'pending')
            ->orderBy('sr.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reviews by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('sr')
            ->where('sr.status = :status')
            ->setParameter('status', $status)
            ->orderBy('sr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reviews by type and status
     */
    public function findByTypeAndStatus(string $reviewType, string $status): array
    {
        return $this->createQueryBuilder('sr')
            ->where('sr.reviewType = :reviewType')
            ->andWhere('sr.status = :status')
            ->setParameter('reviewType', $reviewType)
            ->setParameter('status', $status)
            ->orderBy('sr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get average quality score for a submission
     */
    public function getAverageQualityScore(PropertySubmission $submission): ?float
    {
        $result = $this->createQueryBuilder('sr')
            ->select('AVG(sr.qualityScore)')
            ->where('sr.submission = :submission')
            ->andWhere('sr.qualityScore IS NOT NULL')
            ->andWhere('sr.status = :approvedStatus')
            ->setParameter('submission', $submission)
            ->setParameter('approvedStatus', 'approved')
            ->getQuery()
            ->getSingleScalarResult();
            
        return $result ? round((float)$result, 2) : null;
    }

    /**
     * Count reviews by submission
     */
    public function countBySubmission(PropertySubmission $submission): int
    {
        return $this->createQueryBuilder('sr')
            ->select('COUNT(sr.id)')
            ->where('sr.submission = :submission')
            ->setParameter('submission', $submission)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count approved reviews by submission
     */
    public function countApprovedBySubmission(PropertySubmission $submission): int
    {
        return $this->createQueryBuilder('sr')
            ->select('COUNT(sr.id)')
            ->where('sr.submission = :submission')
            ->andWhere('sr.status = :approvedStatus')
            ->setParameter('submission', $submission)
            ->setParameter('approvedStatus', 'approved')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find recent reviews for dashboard
     */
    public function findRecentReviews(int $limit = 10): array
    {
        return $this->createQueryBuilder('sr')
            ->orderBy('sr.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reviews that need follow-up
     */
    public function findNeedingFollowUp(): array
    {
        $twoDaysAgo = new \DateTime('-2 days');
        
        return $this->createQueryBuilder('sr')
            ->where('sr.status = :pendingStatus')
            ->andWhere('sr.createdAt < :twoDaysAgo')
            ->setParameter('pendingStatus', 'pending')
            ->setParameter('twoDaysAgo', $twoDaysAgo)
            ->orderBy('sr.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get review statistics for admin dashboard
     */
    public function getReviewStatistics(): array
    {
        $qb = $this->createQueryBuilder('sr');
        
        $statusStats = $this->createQueryBuilder('sr')
            ->select('sr.status, COUNT(sr.id) as review_count')
            ->groupBy('sr.status')
            ->getQuery()
            ->getResult();
            
        $typeStats = $this->createQueryBuilder('sr')
            ->select('sr.reviewType, COUNT(sr.id) as review_count')
            ->groupBy('sr.reviewType')
            ->getQuery()
            ->getResult();
            
        $averageScore = $this->createQueryBuilder('sr')
            ->select('AVG(sr.qualityScore)')
            ->where('sr.qualityScore IS NOT NULL')
            ->andWhere('sr.status = :approvedStatus')
            ->setParameter('approvedStatus', 'approved')
            ->getQuery()
            ->getSingleScalarResult();
        
        return [
            'total_reviews' => $qb->select('COUNT(sr.id)')->getQuery()->getSingleScalarResult(),
            'pending_reviews' => $qb->select('COUNT(sr.id)')
                ->where('sr.status = :pendingStatus')
                ->setParameter('pendingStatus', 'pending')
                ->getQuery()->getSingleScalarResult(),
            'approved_reviews' => $qb->select('COUNT(sr.id)')
                ->where('sr.status = :approvedStatus')
                ->setParameter('approvedStatus', 'approved')
                ->getQuery()->getSingleScalarResult(),
            'rejected_reviews' => $qb->select('COUNT(sr.id)')
                ->where('sr.status = :rejectedStatus')
                ->setParameter('rejectedStatus', 'rejected')
                ->getQuery()->getSingleScalarResult(),
            'average_quality_score' => $averageScore ? round((float)$averageScore, 2) : null,
            'status_breakdown' => $statusStats,
            'type_breakdown' => $typeStats,
            'reviews_needing_followup' => count($this->findNeedingFollowUp())
        ];
    }

    /**
     * Find top reviewers by activity
     */
    public function findTopReviewers(int $limit = 10): array
    {
        return $this->createQueryBuilder('sr')
            ->select('IDENTITY(sr.reviewer) as reviewer_id, COUNT(sr.id) as review_count')
            ->where('sr.reviewer IS NOT NULL')
            ->groupBy('sr.reviewer')
            ->orderBy('review_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reviews with quality issues
     */
    public function findLowQualityReviews(float $threshold = 3.0): array
    {
        return $this->createQueryBuilder('sr')
            ->where('sr.qualityScore < :threshold')
            ->andWhere('sr.qualityScore IS NOT NULL')
            ->setParameter('threshold', $threshold)
            ->orderBy('sr.qualityScore', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reviews by country for geographical analysis
     */
    public function getReviewsByCountry(): array
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT ps.country, COUNT(sr.id) as review_count
                FROM App\Entity\SubmissionReview sr
                JOIN sr.submission ps
                WHERE sr.status = :approvedStatus
                GROUP BY ps.country
                ORDER BY review_count DESC
            ')
            ->setParameter('approvedStatus', 'approved')
            ->getResult();
    }

    /**
     * Check if a reviewer has already reviewed a submission
     */
    public function hasReviewerReviewedSubmission(ContributorProfile $reviewer, PropertySubmission $submission): bool
    {
        $count = $this->createQueryBuilder('sr')
            ->select('COUNT(sr.id)')
            ->where('sr.reviewer = :reviewer')
            ->andWhere('sr.submission = :submission')
            ->setParameter('reviewer', $reviewer)
            ->setParameter('submission', $submission)
            ->getQuery()
            ->getSingleScalarResult();
            
        return $count > 0;
    }
}
