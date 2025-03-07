<?php

namespace App\Controller\Public;

use App\Repository\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class BlogController extends AbstractController
{
    public function __construct(
        private BlogPostRepository $blogPostRepository,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/blog', name: 'app_blog_index')]
    public function index(Request $request): Response
    {
        $category = $request->query->get('category');
        $query = $request->query->get('q');
        $locale = $request->getLocale();

        if ($query) {
            $posts = $this->blogPostRepository->search($query, $locale);
        } elseif ($category) {
            $posts = $this->blogPostRepository->findByCategory($category, $locale);
        } else {
            $posts = $this->blogPostRepository->findPublished($locale);
        }

        // Проверяваме и обновяваме slug-овете ако са празни
        foreach ($posts as $post) {
            if (empty($post->getSlug())) {
                $post->updateSlug();
                $this->blogPostRepository->save($post);
            }
        }

        $pagination = $this->paginator->paginate(
            $posts,
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('blog/index.html.twig', [
            'posts' => $pagination,
            'categories' => $this->blogPostRepository->getCategories(),
            'currentCategory' => $category,
            'searchQuery' => $query
        ]);
    }

    #[Route('/blog/{slug}', name: 'app_blog_show')]
    public function show(string $slug): Response
    {
        $post = $this->blogPostRepository->findOneBy(['slug' => $slug, 'isPublished' => true]);

        if (!$post) {
            throw $this->createNotFoundException('Статията не е намерена');
        }

        $latestPosts = $this->blogPostRepository->findLatest(3);

        return $this->render('blog/show.html.twig', [
            'post' => $post,
            'latestPosts' => $latestPosts,
            'categories' => $this->blogPostRepository->getCategories()
        ]);
    }
} 