<?php

namespace App\Repository;

use App\Entity\BlogPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BlogPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPost::class);
    }

    public function save(BlogPost $blogPost): void
    {
        $this->_em->persist($blogPost);
        $this->_em->flush();
    }

    public function remove(BlogPost $blogPost): void
    {
        $this->_em->remove($blogPost);
        $this->_em->flush();
    }

    public function findPublished(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.status = :status')
            ->setParameter('status', 'published')
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByLanguage(string $language): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.language = :language')
            ->setParameter('language', $language)
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPopular(int $limit = 5): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.status = :status')
            ->setParameter('status', 'published')
            ->orderBy('b.views', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
} 