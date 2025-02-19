<?php

namespace App\Controller\Admin;

use App\Entity\Faq;
use App\Form\Admin\FaqType;
use App\Repository\FaqRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/admin/faq')]
#[IsGranted('ROLE_ADMIN')]
class FaqController extends AbstractController
{
    public function __construct(
        private FaqRepository $faqRepository,
        private EntityManagerInterface $entityManager,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/', name: 'admin_faq_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $queryBuilder = $this->faqRepository->createQueryBuilder('f')
            ->orderBy('f.position', 'ASC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/faq/index.html.twig', [
            'faqs' => $pagination,
            'categories' => $this->faqRepository->getCategories()
        ]);
    }

    #[Route('/new', name: 'admin_faq_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $faq = new Faq();
        $form = $this->createForm(FaqType::class, $faq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Задаваме позиция в края на списъка
            $lastPosition = $this->faqRepository->createQueryBuilder('f')
                ->select('MAX(f.position)')
                ->getQuery()
                ->getSingleScalarResult();
            
            $faq->setPosition($lastPosition ? $lastPosition + 1 : 0);
            
            $this->faqRepository->save($faq);

            $this->addFlash('success', 'Въпросът беше добавен успешно');

            return $this->redirectToRoute('admin_faq_index');
        }

        return $this->render('admin/faq/new.html.twig', [
            'faq' => $faq,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_faq_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Faq $faq): Response
    {
        $form = $this->createForm(FaqType::class, $faq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Въпросът беше редактиран успешно');

            return $this->redirectToRoute('admin_faq_index');
        }

        return $this->render('admin/faq/edit.html.twig', [
            'faq' => $faq,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_faq_delete', methods: ['POST'])]
    public function delete(Request $request, Faq $faq): Response
    {
        if ($this->isCsrfTokenValid('delete'.$faq->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($faq);
            $this->entityManager->flush();

            $this->addFlash('success', 'Въпросът беше изтрит успешно');
        }

        return $this->redirectToRoute('admin_faq_index');
    }

    #[Route('/{id}/toggle-active', name: 'admin_faq_toggle_active', methods: ['POST'])]
    public function toggleActive(Faq $faq): Response
    {
        $faq->setIsActive(!$faq->isActive());
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'active' => $faq->isActive(),
            'message' => $faq->isActive() ? 'Въпросът е активиран' : 'Въпросът е деактивиран'
        ]);
    }

    #[Route('/reorder', name: 'admin_faq_reorder', methods: ['POST'])]
    public function reorder(Request $request): Response
    {
        $positions = $request->request->all('positions');
        $this->faqRepository->reorder($positions);

        return $this->json(['success' => true]);
    }
} 