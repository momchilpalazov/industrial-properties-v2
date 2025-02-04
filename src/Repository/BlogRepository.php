<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;
use App\Entity\Blog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BlogRepository extends ServiceEntityRepository
{
    private Connection $connection;

    public function __construct(ManagerRegistry $registry, Connection $connection)
    {
        parent::__construct($registry, Blog::class);
        $this->connection = $connection;
    }

    public function create(object $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function update(object $entity): void
    {
        $this->_em->flush();
    }

    public function delete(object $entity): void
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

    public function searchPosts(array $criteria): array
    {
        $qb = $this->createQueryBuilder('b')
            ->select('b')
            ->where('b.status = :status')
            ->setParameter('status', 'published');

        if (!empty($criteria['category'])) {
            $qb->innerJoin('b.categories', 'c')
               ->andWhere('c.id = :category_id')
               ->setParameter('category_id', $criteria['category']);
        }

        if (!empty($criteria['tag'])) {
            $qb->innerJoin('b.tags', 't')
               ->andWhere('t.id = :tag_id')
               ->setParameter('tag_id', $criteria['tag']);
        }

        if (!empty($criteria['language'])) {
            $qb->andWhere('b.language = :language')
               ->setParameter('language', $criteria['language']);
        }

        $qb->orderBy('b.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function getPostBySlug(string $slug, string $language): ?Blog
    {
        return $this->createQueryBuilder('b')
            ->where('b.slug = :slug')
            ->andWhere('b.language = :language')
            ->andWhere('b.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('language', $language)
            ->setParameter('status', 'published')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getLatestPosts(int $limit, string $language): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.status = :status')
            ->andWhere('b.language = :language')
            ->setParameter('status', 'published')
            ->setParameter('language', $language)
            ->orderBy('b.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getAllCategories(string $language): array
    {
        $qb = $this->createQueryBuilder()
            ->select('c.*')
            ->from('blog_categories', 'c')
            ->where('c.language = :language')
            ->setParameter('language', $language)
            ->orderBy('c.name');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function getPopularTags(string $language, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder()
            ->select('t.*, COUNT(pt.tag_id) as post_count')
            ->from('blog_tags', 't')
            ->innerJoin(
                't',
                'blog_post_tags',
                'pt',
                't.id = pt.tag_id'
            )
            ->where('t.language = :language')
            ->setParameter('language', $language)
            ->groupBy('t.id')
            ->orderBy('post_count', 'DESC')
            ->setMaxResults($limit);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function getPostComments(int $postId): array
    {
        $qb = $this->createQueryBuilder()
            ->select('c.*')
            ->from('blog_comments', 'c')
            ->where('c.post_id = :post_id')
            ->andWhere('c.status = :status')
            ->setParameter('post_id', $postId)
            ->setParameter('status', 'approved')
            ->orderBy('c.created_at', 'DESC');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function incrementViewCount(int $postId): bool
    {
        return (bool) $this->connection->executeStatement(
            'UPDATE ' . $this->table . ' SET views = views + 1 WHERE id = ?',
            [$postId]
        );
    }

    public function getRelatedPosts(array $post, int $limit): array
    {
        // Вземаме постове със същите категории или тагове
        $qb = $this->createQueryBuilder()
            ->select('DISTINCT p.*, pi.image_path')
            ->from($this->table, 'p')
            ->leftJoin(
                'p',
                'blog_images',
                'pi',
                'p.id = pi.post_id AND pi.is_main = 1'
            )
            ->leftJoin(
                'p',
                'blog_post_categories',
                'pc1',
                'p.id = pc1.post_id'
            )
            ->leftJoin(
                'p',
                'blog_post_tags',
                'pt1',
                'p.id = pt1.post_id'
            )
            ->where('p.id != :post_id')
            ->andWhere('p.language = :language')
            ->andWhere('p.status = :status')
            ->andWhere(
                $qb->expr()->orX(
                    'pc1.category_id IN (SELECT category_id FROM blog_post_categories WHERE post_id = :post_id)',
                    'pt1.tag_id IN (SELECT tag_id FROM blog_post_tags WHERE post_id = :post_id)'
                )
            )
            ->setParameter('post_id', $post['id'])
            ->setParameter('language', $post['language'])
            ->setParameter('status', 'published')
            ->orderBy('p.created_at', 'DESC')
            ->setMaxResults($limit);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function addComment(int $postId, array $data): int
    {
        $this->connection->insert('blog_comments', [
            'post_id' => $postId,
            'author_name' => $data['name'],
            'author_email' => $data['email'],
            'content' => $data['content'],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return (int) $this->connection->lastInsertId();
    }
} 