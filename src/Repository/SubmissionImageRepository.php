<?php

namespace App\Repository;

use App\Entity\SubmissionImage;
use App\Entity\PropertySubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubmissionImage>
 */
class SubmissionImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubmissionImage::class);
    }

    /**
     * Find all images for a specific submission
     */
    public function findBySubmission(PropertySubmission $submission): array
    {
        return $this->createQueryBuilder('si')
            ->where('si.submission = :submission')
            ->setParameter('submission', $submission)
            ->orderBy('si.isPrimary', 'DESC')
            ->addOrderBy('si.uploadedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find the primary image for a submission
     */
    public function findPrimaryBySubmission(PropertySubmission $submission): ?SubmissionImage
    {
        return $this->createQueryBuilder('si')
            ->where('si.submission = :submission')
            ->andWhere('si.isPrimary = true')
            ->setParameter('submission', $submission)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find images by type for a submission
     */
    public function findBySubmissionAndType(PropertySubmission $submission, string $imageType): array
    {
        return $this->createQueryBuilder('si')
            ->where('si.submission = :submission')
            ->andWhere('si.imageType = :imageType')
            ->setParameter('submission', $submission)
            ->setParameter('imageType', $imageType)
            ->orderBy('si.uploadedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find unprocessed images for AI analysis
     */
    public function findUnprocessed(int $limit = 50): array
    {
        return $this->createQueryBuilder('si')
            ->where('si.isProcessed = false')
            ->orderBy('si.uploadedAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count images by submission
     */
    public function countBySubmission(PropertySubmission $submission): int
    {
        return $this->createQueryBuilder('si')
            ->select('COUNT(si.id)')
            ->where('si.submission = :submission')
            ->setParameter('submission', $submission)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get total file size for a submission
     */
    public function getTotalFileSizeBySubmission(PropertySubmission $submission): int
    {
        $result = $this->createQueryBuilder('si')
            ->select('SUM(si.fileSize)')
            ->where('si.submission = :submission')
            ->setParameter('submission', $submission)
            ->getQuery()
            ->getSingleScalarResult();
            
        return $result ? (int)$result : 0;
    }

    /**
     * Find large images that need compression
     */
    public function findLargeImages(int $maxFileSize = 5242880): array // 5MB default
    {
        return $this->createQueryBuilder('si')
            ->where('si.fileSize > :maxSize')
            ->setParameter('maxSize', $maxFileSize)
            ->orderBy('si.fileSize', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get image statistics for admin dashboard
     */
    public function getImageStatistics(): array
    {
        $qb = $this->createQueryBuilder('si');
        
        return [
            'total_images' => $qb->select('COUNT(si.id)')->getQuery()->getSingleScalarResult(),
            'processed_images' => $qb->select('COUNT(si.id)')
                ->where('si.isProcessed = true')
                ->getQuery()->getSingleScalarResult(),
            'total_size_mb' => round(
                $qb->select('SUM(si.fileSize)')->getQuery()->getSingleScalarResult() / 1048576, 
                2
            ),
            'avg_images_per_submission' => round(
                $this->getEntityManager()
                    ->createQuery('
                        SELECT AVG(image_count) 
                        FROM (
                            SELECT COUNT(si.id) as image_count 
                            FROM App\Entity\SubmissionImage si 
                            GROUP BY si.submission
                        ) subquery
                    ')
                    ->getSingleScalarResult(),
                1
            )
        ];
    }
}
