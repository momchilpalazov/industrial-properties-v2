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
        $language = $criteria['language'] ?? 'bg';
        unset($criteria['language']);
        
        return $this->blogRepository->findBy(array_merge($criteria, ['language' => $language]));
    }

    public function getLatestPosts(int $limit, string $language = 'bg'): array
    {
        return $this->blogRepository->findLatest($limit, $language);
    }

    public function getPostBySlug(string $slug, string $language = 'bg'): ?BlogPost
    {
        return $this->blogRepository->findOneBy([
            'slug' => $slug,
            'language' => $language,
            'isPublished' => true
        ]);
    }

    public function incrementViewCount(BlogPost $post): void
    {
        $post->incrementViews();
        $this->blogRepository->save($post);
    }

    public function getPopularPosts(string $language = 'bg', int $limit = 5): array
    {
        return $this->blogRepository->findBy(
            ['language' => $language, 'isPublished' => true],
            ['views' => 'DESC'],
            $limit
        );
    }
} 