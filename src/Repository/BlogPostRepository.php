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

    public function save(BlogPost $blogPost, bool $flush = true): void
    {
        // Генерираме slug ако е празен
        if (empty($blogPost->getSlug())) {
            $blogPost->updateSlug();
        }

        $this->_em->persist($blogPost);
        
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(BlogPost $blogPost): void
    {
        $this->_em->remove($blogPost);
        $this->_em->flush();
    }

    public function findPublished(string $language = 'bg', string $category = null)
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.isPublished = :published')
            ->andWhere('b.language = :language')
            ->setParameter('published', true)
            ->setParameter('language', $language)
            ->orderBy('b.publishedAt', 'DESC');

        if ($category) {
            $qb->andWhere('b.category = :category')
               ->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }

    public function findLatest(int $limit = 5, string $language = 'bg')
    {
        return $this->createQueryBuilder('b')
            ->where('b.isPublished = :published')
            ->andWhere('b.language = :language')
            ->setParameter('published', true)
            ->setParameter('language', $language)
            ->orderBy('b.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(string $category, string $language = 'bg')
    {
        return $this->createQueryBuilder('b')
            ->where('b.category = :category')
            ->andWhere('b.isPublished = :published')
            ->andWhere('b.language = :language')
            ->setParameter('category', $category)
            ->setParameter('published', true)
            ->setParameter('language', $language)
            ->orderBy('b.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function search(string $query, string $language = 'bg')
    {
        return $this->createQueryBuilder('b')
            ->where('b.titleBg LIKE :query')
            ->orWhere('b.titleEn LIKE :query')
            ->orWhere('b.contentBg LIKE :query')
            ->orWhere('b.contentEn LIKE :query')
            ->andWhere('b.isPublished = :published')
            ->andWhere('b.language = :language')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('published', true)
            ->setParameter('language', $language)
            ->orderBy('b.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getCategories(): array
    {
        return [
            'industry_articles' => 'blog.categories.industry_articles',
            'sector_news' => 'blog.categories.sector_news',
            'investor_tips' => 'blog.categories.investor_tips'
        ];
    }
} 