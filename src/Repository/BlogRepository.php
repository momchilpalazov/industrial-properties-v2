<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

class BlogRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
        $this->table = 'blog_posts';
    }

    public function searchPosts(array $criteria): array
    {
        $qb = $this->createQueryBuilder()
            ->select('p.*, pi.image_path')
            ->from($this->table, 'p')
            ->leftJoin(
                'p',
                'blog_images',
                'pi',
                'p.id = pi.post_id AND pi.is_main = 1'
            )
            ->where('p.status = :status')
            ->setParameter('status', 'published');

        if (!empty($criteria['category'])) {
            $qb->innerJoin(
                'p',
                'blog_post_categories',
                'pc',
                'p.id = pc.post_id'
            )
            ->andWhere('pc.category_id = :category_id')
            ->setParameter('category_id', $criteria['category']);
        }

        if (!empty($criteria['tag'])) {
            $qb->innerJoin(
                'p',
                'blog_post_tags',
                'pt',
                'p.id = pt.post_id'
            )
            ->andWhere('pt.tag_id = :tag_id')
            ->setParameter('tag_id', $criteria['tag']);
        }

        if (!empty($criteria['language'])) {
            $qb->andWhere('p.language = :language')
               ->setParameter('language', $criteria['language']);
        }

        $qb->orderBy('p.created_at', 'DESC');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function getPostBySlug(string $slug, string $language): ?array
    {
        $qb = $this->createQueryBuilder()
            ->select('p.*, pi.image_path')
            ->from($this->table, 'p')
            ->leftJoin(
                'p',
                'blog_images',
                'pi',
                'p.id = pi.post_id AND pi.is_main = 1'
            )
            ->where('p.slug = :slug')
            ->andWhere('p.language = :language')
            ->andWhere('p.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('language', $language)
            ->setParameter('status', 'published');

        $result = $qb->executeQuery()->fetchAssociative();
        return $result ?: null;
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