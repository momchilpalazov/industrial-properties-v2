<?php

namespace App\Controller\Admin;

use App\Entity\BlogPost;
use App\Form\Admin\BlogPostType;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/blog')]
#[IsGranted('ROLE_ADMIN')]
class BlogController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BlogPostRepository $blogPostRepository,
        private PaginatorInterface $paginator,
        private SluggerInterface $slugger
    ) {}

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
            $post->setSlug($this->slugger->slug($post->getTitle())->lower());
            $this->blogPostRepository->save($post);

            $this->addFlash('success', 'Публикацията беше създадена успешно');
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
            $post->setSlug($this->slugger->slug($post->getTitle())->lower());
            $this->blogPostRepository->save($post);

            $this->addFlash('success', 'Публикацията беше редактирана успешно');
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
            $this->blogPostRepository->remove($post);
            $this->addFlash('success', 'Публикацията беше изтрита успешно');
        }

        return $this->redirectToRoute('admin_blog_index');
    }
} 