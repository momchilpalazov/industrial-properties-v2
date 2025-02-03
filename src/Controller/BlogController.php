<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\BlogService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class BlogController extends AbstractController
{
    private BlogService $blogService;

    public function __construct(
        Environment $twig,
        BlogService $blogService
    ) {
        parent::__construct($twig);
        $this->blogService = $blogService;
    }

    public function index(Request $request): Response
    {
        $page = (int) $request->query->get('page', 1);
        $category = $request->query->get('category');
        $tag = $request->query->get('tag');
        $currentLanguage = $request->getLocale();

        $criteria = array_filter([
            'category' => $category,
            'tag' => $tag,
            'language' => $currentLanguage
        ]);

        $posts = $this->blogService->searchPosts($criteria);
        $totalPosts = count($posts);
        $itemsPerPage = 9;
        $totalPages = ceil($totalPosts / $itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        $currentPagePosts = array_slice($posts, $offset, $itemsPerPage);

        $categories = $this->blogService->getAllCategories($currentLanguage);
        $popularTags = $this->blogService->getPopularTags($currentLanguage);

        return $this->render('blog/index.html.twig', [
            'posts' => $currentPagePosts,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_posts' => $totalPosts,
            'categories' => $categories,
            'popular_tags' => $popularTags,
            'current_category' => $category,
            'current_tag' => $tag,
            'current_language' => $currentLanguage
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $currentLanguage = $request->getLocale();
        $post = $this->blogService->getPostBySlug($slug, $currentLanguage);

        if (!$post) {
            // TODO: Implement proper 404 handling
            return new Response('Post not found', Response::HTTP_NOT_FOUND);
        }

        // Увеличаваме броя преглеждания
        $this->blogService->incrementViewCount($post['id']);

        // Вземаме подобни публикации
        $relatedPosts = $this->blogService->getRelatedPosts($post, 3);

        // Вземаме коментари
        $comments = $this->blogService->getPostComments($post['id']);

        return $this->render('blog/show.html.twig', [
            'post' => $post,
            'related_posts' => $relatedPosts,
            'comments' => $comments,
            'current_language' => $currentLanguage
        ]);
    }

    public function comment(Request $request, int $postId): Response
    {
        if (!$request->isMethod('POST')) {
            return $this->json(['error' => 'Method not allowed'], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $data = json_decode($request->getContent(), true);
        $result = $this->blogService->addComment($postId, $data);

        if ($result) {
            return $this->json(['message' => 'Comment added successfully']);
        }

        return $this->json(
            ['error' => 'Failed to add comment'],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
} 