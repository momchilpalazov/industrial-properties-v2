<?php

namespace App\Controller\Admin;

use App\Entity\BlogCategory;
use App\Form\Admin\BlogCategoryType;
use App\Repository\BlogCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/admin/blog-categories')]
#[IsGranted('ROLE_ADMIN')]
class BlogCategoryController extends AbstractController
{
    #[Route('/', name: 'admin_blog_category_index', methods: ['GET'])]
    public function index(BlogCategoryRepository $blogCategoryRepository): Response
    {
        $categories = $blogCategoryRepository->findBy([], ['position' => 'ASC', 'name' => 'ASC']);

        return $this->render('admin/blog_category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'admin_blog_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, BlogCategoryRepository $repository): Response
    {
        $blogCategory = new BlogCategory();
        
        // Задаваме следващата позиция
        $blogCategory->setPosition($repository->getNextPosition());
        
        $form = $this->createForm(BlogCategoryType::class, $blogCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Генерираме slug ако не е зададен
            if (empty($blogCategory->getSlug())) {
                $slugger = new AsciiSlugger();
                $slug = strtolower($slugger->slug($blogCategory->getName()));
                $blogCategory->setSlug($slug);
            }
            
            $entityManager->persist($blogCategory);
            $entityManager->flush();

            $this->addFlash('success', 'Категорията беше създадена успешно.');
            return $this->redirectToRoute('admin_blog_category_index');
        }

        return $this->render('admin/blog_category/new.html.twig', [
            'blog_category' => $blogCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_blog_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BlogCategory $blogCategory, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BlogCategoryType::class, $blogCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Обновяваме updated_at
            $blogCategory->setUpdatedAt(new \DateTimeImmutable());
            
            // Генерираме slug ако не е зададен
            if (empty($blogCategory->getSlug())) {
                $slugger = new AsciiSlugger();
                $slug = strtolower($slugger->slug($blogCategory->getName()));
                $blogCategory->setSlug($slug);
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'Категорията беше редактирана успешно.');
            return $this->redirectToRoute('admin_blog_category_index');
        }

        return $this->render('admin/blog_category/edit.html.twig', [
            'blog_category' => $blogCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_blog_category_delete', methods: ['POST'])]
    public function delete(Request $request, BlogCategory $blogCategory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$blogCategory->getId(), $request->request->get('_token'))) {
            // Проверяваме дали има блог постове свързани с тази категория
            if (!$blogCategory->getBlogPosts()->isEmpty()) {
                $this->addFlash('error', 'Не можете да изтриете категория която се използва от блог постове.');
                return $this->redirectToRoute('admin_blog_category_index');
            }
            
            $entityManager->remove($blogCategory);
            $entityManager->flush();

            $this->addFlash('success', 'Категорията беше изтрита успешно.');
        }

        return $this->redirectToRoute('admin_blog_category_index');
    }

    #[Route('/{id}/toggle-visibility', name: 'admin_blog_category_toggle_visibility', methods: ['POST'])]
    public function toggleVisibility(Request $request, BlogCategory $blogCategory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle-visibility'.$blogCategory->getId(), $request->request->get('_token'))) {
            $blogCategory->setIsVisible(!$blogCategory->isVisible());
            $blogCategory->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
            
            $status = $blogCategory->isVisible() ? 'видима' : 'скрита';
            $this->addFlash('success', "Категорията е вече {$status}.");
        }
        
        return $this->redirectToRoute('admin_blog_category_index');
    }
}
