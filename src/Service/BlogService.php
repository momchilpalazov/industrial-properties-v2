<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\BlogRepository;

class BlogService
{
    private BlogRepository $blogRepository;

    public function __construct(BlogRepository $blogRepository)
    {
        $this->blogRepository = $blogRepository;
    }

    public function searchPosts(array $criteria): array
    {
        return $this->blogRepository->searchPosts($criteria);
    }

    public function getLatestPosts(int $limit, string $language): array
    {
        return $this->blogRepository->searchPosts([
            'language' => $language,
            'limit' => $limit
        ]);
    }

    public function getPostBySlug(string $slug, string $language): ?array
    {
        return $this->blogRepository->getPostBySlug($slug, $language);
    }

    public function getAllCategories(string $language): array
    {
        return $this->blogRepository->getAllCategories($language);
    }

    public function getPopularTags(string $language, int $limit = 10): array
    {
        return $this->blogRepository->getPopularTags($language, $limit);
    }

    public function getPostComments(int $postId): array
    {
        return $this->blogRepository->getPostComments($postId);
    }

    public function incrementViewCount(int $postId): bool
    {
        return $this->blogRepository->incrementViewCount($postId);
    }

    public function getRelatedPosts(array $post, int $limit): array
    {
        return $this->blogRepository->getRelatedPosts($post, $limit);
    }

    public function addComment(int $postId, array $data): bool
    {
        // Валидация на входните данни
        if (empty($data['name']) || empty($data['email']) || empty($data['content'])) {
            return false;
        }

        // Валидация на имейл
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Базова защита от спам
        if (strlen($data['content']) > 1000 || 
            preg_match('/https?:\/\//i', $data['content']) > 2) {
            return false;
        }

        try {
            $commentId = $this->blogRepository->addComment($postId, [
                'name' => strip_tags($data['name']),
                'email' => $data['email'],
                'content' => strip_tags($data['content'])
            ]);

            return $commentId > 0;
        } catch (\Exception $e) {
            // TODO: Добавяне на логване
            return false;
        }
    }

    public function formatPostContent(string $content): string
    {
        // Премахване на нежелани HTML тагове
        $content = strip_tags($content, '<p><br><strong><em><ul><ol><li><h2><h3><h4><blockquote>');

        // Конвертиране на URL адреси в линкове
        $content = preg_replace(
            '/(https?:\/\/[^\s<]+)/i',
            '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
            $content
        );

        // Форматиране на нови редове
        $content = nl2br($content);

        return $content;
    }

    public function generateExcerpt(string $content, int $length = 150): string
    {
        // Премахване на HTML таговете
        $text = strip_tags($content);
        
        // Премахване на множество интервали
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Съкращаване на текста
        if (mb_strlen($text) > $length) {
            $text = mb_substr($text, 0, $length);
            $text = mb_substr($text, 0, mb_strrpos($text, ' ')) . '...';
        }
        
        return $text;
    }
} 