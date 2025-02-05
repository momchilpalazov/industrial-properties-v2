<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\BlogPostRepository;
use App\Entity\BlogPost;

class BlogService
{
    private BlogPostRepository $blogRepository;

    public function __construct(BlogPostRepository $blogRepository)
    {
        $this->blogRepository = $blogRepository;
    }

    public function searchPosts(array $criteria): array
    {
        return $this->blogRepository->findBy($criteria);
    }

    public function getLatestPosts(int $limit, string $language): array
    {
        return $this->blogRepository->findBy(
            ['language' => $language, 'status' => 'published'],
            ['createdAt' => 'DESC'],
            $limit
        );
    }

    public function getPostBySlug(string $slug, string $language): ?BlogPost
    {
        return $this->blogRepository->findOneBy([
            'slug' => $slug,
            'language' => $language,
            'status' => 'published'
        ]);
    }

    public function incrementViewCount(BlogPost $post): void
    {
        $post->incrementViews();
        $this->blogRepository->save($post);
    }

    public function getPopularPosts(string $language, int $limit = 5): array
    {
        return $this->blogRepository->findBy(
            ['language' => $language, 'status' => 'published'],
            ['views' => 'DESC'],
            $limit
        );
    }
} 