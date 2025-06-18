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
            ->setParameter('published', true)
            ->orderBy('b.publishedAt', 'DESC');

        // Филтрираме само статии които имат съдържание на избрания език
        if ($language === 'en') {
            $qb->andWhere('b.titleEn IS NOT NULL')
               ->andWhere('b.titleEn != :empty')
               ->andWhere('b.contentEn IS NOT NULL')
               ->andWhere('b.contentEn != :empty')
               ->setParameter('empty', '');
        } else {
            $qb->andWhere('b.titleBg IS NOT NULL')
               ->andWhere('b.titleBg != :empty')
               ->andWhere('b.contentBg IS NOT NULL')
               ->andWhere('b.contentBg != :empty')
               ->setParameter('empty', '');
        }

        if ($category) {
            $qb->andWhere('b.category = :category')
               ->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }

    public function findLatest(int $limit = 5, string $language = 'bg')
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('b.publishedAt', 'DESC')
            ->setMaxResults($limit);

        // Филтрираме само статии които имат съдържание на избрания език
        if ($language === 'en') {
            $qb->andWhere('b.titleEn IS NOT NULL')
               ->andWhere('b.titleEn != :empty')
               ->andWhere('b.contentEn IS NOT NULL')
               ->andWhere('b.contentEn != :empty')
               ->setParameter('empty', '');
        } else {
            $qb->andWhere('b.titleBg IS NOT NULL')
               ->andWhere('b.titleBg != :empty')
               ->andWhere('b.contentBg IS NOT NULL')
               ->andWhere('b.contentBg != :empty')
               ->setParameter('empty', '');
        }

        return $qb->getQuery()->getResult();
    }

    public function findByCategory(string $category, string $language = 'bg')
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.category = :category')
            ->andWhere('b.isPublished = :published')
            ->setParameter('category', $category)
            ->setParameter('published', true)
            ->orderBy('b.publishedAt', 'DESC');

        // Филтрираме само статии които имат съдържание на избрания език
        if ($language === 'en') {
            $qb->andWhere('b.titleEn IS NOT NULL')
               ->andWhere('b.titleEn != :empty')
               ->andWhere('b.contentEn IS NOT NULL')
               ->andWhere('b.contentEn != :empty')
               ->setParameter('empty', '');
        } else {
            $qb->andWhere('b.titleBg IS NOT NULL')
               ->andWhere('b.titleBg != :empty')
               ->andWhere('b.contentBg IS NOT NULL')
               ->andWhere('b.contentBg != :empty')
               ->setParameter('empty', '');
        }

        return $qb->getQuery()->getResult();
    }

    public function search(string $query, string $language = 'bg')
    {
        $qb = $this->createQueryBuilder('b')
            ->andWhere('b.isPublished = :published')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('published', true)
            ->orderBy('b.publishedAt', 'DESC');

        // Търсим в полетата според избрания език
        if ($language === 'en') {
            $qb->andWhere('(b.titleEn LIKE :query OR b.contentEn LIKE :query)')
               ->andWhere('b.titleEn IS NOT NULL')
               ->andWhere('b.titleEn != :empty')
               ->andWhere('b.contentEn IS NOT NULL')
               ->andWhere('b.contentEn != :empty')
               ->setParameter('empty', '');
        } else {
            $qb->andWhere('(b.titleBg LIKE :query OR b.contentBg LIKE :query)')
               ->andWhere('b.titleBg IS NOT NULL')
               ->andWhere('b.titleBg != :empty')
               ->andWhere('b.contentBg IS NOT NULL')
               ->andWhere('b.contentBg != :empty')
               ->setParameter('empty', '');
        }

        return $qb->getQuery()->getResult();
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