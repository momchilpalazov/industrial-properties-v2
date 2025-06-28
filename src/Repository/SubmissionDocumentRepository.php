<?php

namespace App\Repository;

use App\Entity\SubmissionDocument;
use App\Entity\PropertySubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubmissionDocument>
 */
class SubmissionDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubmissionDocument::class);
    }

    /**
     * Find all documents for a specific submission
     */
    public function findBySubmission(PropertySubmission $submission): array
    {
        return $this->createQueryBuilder('sd')
            ->where('sd.submission = :submission')
            ->setParameter('submission', $submission)
            ->orderBy('sd.documentType', 'ASC')
            ->addOrderBy('sd.uploadedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find documents by type for a submission
     */
    public function findBySubmissionAndType(PropertySubmission $submission, string $documentType): array
    {
        return $this->createQueryBuilder('sd')
            ->where('sd.submission = :submission')
            ->andWhere('sd.documentType = :documentType')
            ->setParameter('submission', $submission)
            ->setParameter('documentType', $documentType)
            ->orderBy('sd.uploadedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find verified documents for a submission
     */
    public function findVerifiedBySubmission(PropertySubmission $submission): array
    {
        return $this->createQueryBuilder('sd')
            ->where('sd.submission = :submission')
            ->andWhere('sd.isVerified = true')
            ->orderBy('sd.documentType', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find unprocessed documents for AI analysis
     */
    public function findUnprocessed(int $limit = 50): array
    {
        return $this->createQueryBuilder('sd')
            ->where('sd.isProcessed = false')
            ->orderBy('sd.uploadedAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find documents that need verification
     */
    public function findUnverified(): array
    {
        return $this->createQueryBuilder('sd')
            ->where('sd.isVerified = false')
            ->andWhere('sd.documentType IN (:importantTypes)')
            ->setParameter('importantTypes', [
                'permit', 'certificate', 'property_deed', 
                'energy_audit', 'environmental_report'
            ])
            ->orderBy('sd.uploadedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count documents by submission
     */
    public function countBySubmission(PropertySubmission $submission): int
    {
        return $this->createQueryBuilder('sd')
            ->select('COUNT(sd.id)')
            ->where('sd.submission = :submission')
            ->setParameter('submission', $submission)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count verified documents by submission
     */
    public function countVerifiedBySubmission(PropertySubmission $submission): int
    {
        return $this->createQueryBuilder('sd')
            ->select('COUNT(sd.id)')
            ->where('sd.submission = :submission')
            ->andWhere('sd.isVerified = true')
            ->setParameter('submission', $submission)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get total file size for a submission
     */
    public function getTotalFileSizeBySubmission(PropertySubmission $submission): int
    {
        $result = $this->createQueryBuilder('sd')
            ->select('SUM(sd.fileSize)')
            ->where('sd.submission = :submission')
            ->setParameter('submission', $submission)
            ->getQuery()
            ->getSingleScalarResult();
            
        return $result ? (int)$result : 0;
    }

    /**
     * Find documents with extracted text for search
     */
    public function searchInExtractedText(string $searchTerm): array
    {
        return $this->createQueryBuilder('sd')
            ->where('sd.extractedText IS NOT NULL')
            ->andWhere('LOWER(sd.extractedText) LIKE LOWER(:searchTerm)')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('sd.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find sensitive documents that need special handling
     */
    public function findSensitiveDocuments(): array
    {
        return $this->createQueryBuilder('sd')
            ->where('sd.documentType IN (:sensitiveTypes)')
            ->setParameter('sensitiveTypes', [
                'property_deed', 'lease_agreement', 'valuation_report', 'utility_bill'
            ])
            ->orderBy('sd.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get document statistics for admin dashboard
     */
    public function getDocumentStatistics(): array
    {
        $qb = $this->createQueryBuilder('sd');
        
        $typeStats = $this->createQueryBuilder('sd')
            ->select('sd.documentType, COUNT(sd.id) as doc_count')
            ->groupBy('sd.documentType')
            ->orderBy('doc_count', 'DESC')
            ->getQuery()
            ->getResult();
        
        return [
            'total_documents' => $qb->select('COUNT(sd.id)')->getQuery()->getSingleScalarResult(),
            'verified_documents' => $qb->select('COUNT(sd.id)')
                ->where('sd.isVerified = true')
                ->getQuery()->getSingleScalarResult(),
            'processed_documents' => $qb->select('COUNT(sd.id)')
                ->where('sd.isProcessed = true')
                ->getQuery()->getSingleScalarResult(),
            'total_size_mb' => round(
                $qb->select('SUM(sd.fileSize)')->getQuery()->getSingleScalarResult() / 1048576, 
                2
            ),
            'document_types' => $typeStats,
            'avg_docs_per_submission' => round(
                $this->getEntityManager()
                    ->createQuery('
                        SELECT AVG(doc_count) 
                        FROM (
                            SELECT COUNT(sd.id) as doc_count 
                            FROM App\Entity\SubmissionDocument sd 
                            GROUP BY sd.submission
                        ) subquery
                    ')
                    ->getSingleScalarResult(),
                1
            )
        ];
    }

    /**
     * Find documents by issued authority
     */
    public function findByIssuedBy(string $authority): array
    {
        return $this->createQueryBuilder('sd')
            ->where('sd.issuedBy = :authority')
            ->setParameter('authority', $authority)
            ->orderBy('sd.documentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find expired documents (based on document date and type)
     */
    public function findExpiredDocuments(): array
    {
        $oneYearAgo = new \DateTime('-1 year');
        $twoYearsAgo = new \DateTime('-2 years');
        
        return $this->createQueryBuilder('sd')
            ->where('
                (sd.documentType IN (:shortTermTypes) AND sd.documentDate < :oneYearAgo) OR
                (sd.documentType IN (:longTermTypes) AND sd.documentDate < :twoYearsAgo)
            ')
            ->setParameter('shortTermTypes', ['permit', 'certificate', 'inspection_report'])
            ->setParameter('longTermTypes', ['energy_audit', 'environmental_report'])
            ->setParameter('oneYearAgo', $oneYearAgo)
            ->setParameter('twoYearsAgo', $twoYearsAgo)
            ->orderBy('sd.documentDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
