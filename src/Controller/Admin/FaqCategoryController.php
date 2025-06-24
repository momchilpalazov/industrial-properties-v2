<?php

namespace App\Controller\Admin;

use App\Entity\FaqCategory;
use App\Form\Admin\FaqCategoryType;
use App\Repository\FaqCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/admin/faq-category')]
#[IsGranted('ROLE_ADMIN')]
class FaqCategoryController extends AbstractController
{
    public function __construct(
        private FaqCategoryRepository $faqCategoryRepository,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/', name: 'admin_faq_category_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $queryBuilder = $this->faqCategoryRepository->createQueryBuilder('fc')
            ->leftJoin('fc.faqs', 'f', 'WITH', 'f.isActive = :active')
            ->addSelect('COUNT(f.id) as activeFaqCount')
            ->setParameter('active', true)
            ->groupBy('fc.id')
            ->orderBy('fc.position', 'ASC')
            ->addOrderBy('fc.nameBg', 'ASC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render('admin/faq_category/index.html.twig', [
            'categories' => $pagination,
        ]);
    }

    #[Route('/new', name: 'admin_faq_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $category = new FaqCategory();
        $form = $this->createForm(FaqCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Автоматично генериране на slug, ако не е зададен
            if (!$category->getSlug()) {
                $slug = $this->slugger->slug(strtolower($category->getNameBg()))->toString();
                $category->setSlug($slug);
            }

            // Задаване на позиция, ако не е зададена
            if ($category->getPosition() === 0) {
                $nextPosition = $this->faqCategoryRepository->getNextPosition();
                $category->setPosition($nextPosition);
            }

            $this->faqCategoryRepository->save($category);

            $this->addFlash('success', 'FAQ категорията беше създадена успешно.');

            return $this->redirectToRoute('admin_faq_category_index');
        }

        return $this->render('admin/faq_category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_faq_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FaqCategory $category): Response
    {
        $form = $this->createForm(FaqCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Актуализиране на slug при нужда
            if (!$category->getSlug()) {
                $slug = $this->slugger->slug(strtolower($category->getNameBg()))->toString();
                $category->setSlug($slug);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'FAQ категорията беше редактирана успешно.');

            return $this->redirectToRoute('admin_faq_category_index');
        }

        return $this->render('admin/faq_category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_faq_category_delete', methods: ['POST'])]
    public function delete(Request $request, FaqCategory $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            // Проверка дали има FAQ въпроси в тази категория
            if ($category->getFaqs()->count() > 0) {
                $this->addFlash('error', 'Не можете да изтриете категория, която съдържа FAQ въпроси.');
            } else {
                $this->faqCategoryRepository->remove($category);
                $this->addFlash('success', 'FAQ категорията беше изтрита успешно.');
            }
        }

        return $this->redirectToRoute('admin_faq_category_index');
    }

    #[Route('/{id}/toggle-visibility', name: 'admin_faq_category_toggle_visibility', methods: ['POST'])]
    public function toggleVisibility(Request $request, FaqCategory $category): Response
    {
        if ($this->isCsrfTokenValid('toggle-visibility'.$category->getId(), $request->request->get('_token'))) {
            $category->setIsVisible(!$category->isVisible());
            $this->entityManager->flush();

            $status = $category->isVisible() ? 'видима' : 'скрита';
            $this->addFlash('success', "FAQ категорията е вече {$status}.");
        }

        return $this->redirectToRoute('admin_faq_category_index');
    }

    #[Route('/{id}/move-up', name: 'admin_faq_category_move_up', methods: ['POST'])]
    public function moveUp(Request $request, FaqCategory $category): Response
    {
        if ($this->isCsrfTokenValid('move-up'.$category->getId(), $request->request->get('_token'))) {
            $currentPosition = $category->getPosition();

            if ($currentPosition > 0) {
                // Намираме категорията над текущата
                $previousCategory = $this->faqCategoryRepository
                    ->createQueryBuilder('fc')
                    ->where('fc.position < :position')
                    ->setParameter('position', $currentPosition)
                    ->orderBy('fc.position', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();

                if ($previousCategory) {
                    // Разменяме позициите
                    $previousPosition = $previousCategory->getPosition();
                    $previousCategory->setPosition($currentPosition);
                    $category->setPosition($previousPosition);
                    $this->entityManager->flush();

                    $this->addFlash('success', 'Позицията на FAQ категорията беше променена успешно.');
                }
            }
        }

        return $this->redirectToRoute('admin_faq_category_index');
    }

    #[Route('/{id}/move-down', name: 'admin_faq_category_move_down', methods: ['POST'])]
    public function moveDown(Request $request, FaqCategory $category): Response
    {
        if ($this->isCsrfTokenValid('move-down'.$category->getId(), $request->request->get('_token'))) {
            $currentPosition = $category->getPosition();

            // Намираме категорията под текущата
            $nextCategory = $this->faqCategoryRepository
                ->createQueryBuilder('fc')
                ->where('fc.position > :position')
                ->setParameter('position', $currentPosition)
                ->orderBy('fc.position', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($nextCategory) {
                // Разменяме позициите
                $nextPosition = $nextCategory->getPosition();
                $nextCategory->setPosition($currentPosition);
                $category->setPosition($nextPosition);
                $this->entityManager->flush();

                $this->addFlash('success', 'Позицията на FAQ категорията беше променена успешно.');
            }
        }

        return $this->redirectToRoute('admin_faq_category_index');
    }

    #[Route('/reorder', name: 'admin_faq_category_reorder', methods: ['POST'])]
    public function reorder(Request $request): Response
    {
        $positions = $request->request->all('positions');
        $this->faqCategoryRepository->reorderPositions($positions);

        return $this->json(['success' => true]);
    }
}