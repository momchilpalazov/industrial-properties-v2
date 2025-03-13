<?php

namespace App\Controller\Admin;

use App\Entity\BlogPost;
use App\Form\Admin\BlogPostType;
use App\Repository\BlogPostRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/admin/blog')]
#[IsGranted('ROLE_ADMIN')]
class BlogController extends AbstractController
{
    private $entityManager;
    private $blogPostRepository;
    private $fileUploadService;
    private $paginator;
    private $slugger;

    public function __construct(
        BlogPostRepository $blogPostRepository,
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator,
        SluggerInterface $slugger
    ) {
        $this->blogPostRepository = $blogPostRepository;
        $this->entityManager = $entityManager;
        $this->paginator = $paginator;
        $this->slugger = $slugger;
    }

    #[Route('/', name: 'admin_blog_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $queryBuilder = $this->blogPostRepository->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/blog/index.html.twig', [
            'posts' => $pagination,
        ]);
    }

    #[Route('/new', name: 'admin_blog_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $post = new BlogPost();
        $form = $this->createForm(BlogPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Генерираме slug преди записване
            $post->updateSlug();
            
            // Проверяваме дали slug е уникален
            $existingPost = $this->blogPostRepository->findOneBy(['slug' => $post->getSlug()]);
            if ($existingPost) {
                // Ако съществува, добавяме уникален идентификатор
                $post->setSlug($post->getSlug() . '-' . uniqid());
            }

            $this->blogPostRepository->save($post);

            $this->addFlash('success', 'Статията беше създадена успешно');

            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('admin/blog/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_blog_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BlogPost $post): Response
    {
        $form = $this->createForm(BlogPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Генерираме slug преди записване
            $post->updateSlug();
            
            // Проверяваме дали slug е уникален (изключваме текущия пост)
            $existingPost = $this->blogPostRepository->findOneBy(['slug' => $post->getSlug()]);
            if ($existingPost && $existingPost->getId() !== $post->getId()) {
                // Ако съществува, добавяме уникален идентификатор
                $post->setSlug($post->getSlug() . '-' . uniqid());
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Статията беше редактирана успешно');

            return $this->redirectToRoute('admin_blog_index');
        }

        return $this->render('admin/blog/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_blog_delete', methods: ['POST'])]
    public function delete(Request $request, BlogPost $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($post);
            $this->entityManager->flush();

            $this->addFlash('success', 'Статията беше изтрита успешно');
        }

        return $this->redirectToRoute('admin_blog_index');
    }

    #[Route('/{id}/toggle-published', name: 'admin_blog_toggle_published', methods: ['POST'])]
    public function togglePublished(BlogPost $post): Response
    {
        $post->setIsPublished(!$post->isPublished());
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'published' => $post->isPublished(),
            'message' => $post->isPublished() ? 'Статията е публикувана' : 'Статията е скрита'
        ]);
    }
} 