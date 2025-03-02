<?php

namespace App\Repository;

use App\Entity\SocialPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SocialPost>
 *
 * @method SocialPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocialPost[]    findAll()
 * @method SocialPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocialPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialPost::class);
    }

    /**
     * @return SocialPost[] Returns an array of latest social posts
     */
    public function findLatestPosts(int $limit = 10): array
    {
        return $this->createQueryBuilder('sp')
            ->orderBy('sp.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return SocialPost[] Returns an array of social posts for a specific property
     */
    public function findByProperty(int $propertyId): array
    {
        return $this->createQueryBuilder('sp')
            ->andWhere('sp.property = :propertyId')
            ->setParameter('propertyId', $propertyId)
            ->orderBy('sp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 