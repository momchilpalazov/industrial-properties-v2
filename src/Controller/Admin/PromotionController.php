<?php

namespace App\Controller\Admin;

use App\Entity\Property;
use App\Entity\Promotion;
use App\Form\PromotionType;
use App\Repository\PromotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/promotions')]
class PromotionController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private PromotionRepository $promotionRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PromotionRepository $promotionRepository
    ) {
        $this->entityManager = $entityManager;
        $this->promotionRepository = $promotionRepository;
    }

    #[Route('/', name: 'admin_promotion_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/promotion/index.html.twig', [
            'promotions' => $this->promotionRepository->findAll(),
        ]);
    }

    #[Route('/new/{property}', name: 'admin_promotion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Property $property): Response
    {
        $promotion = new Promotion();
        $promotion->setProperty($property);
        
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($promotion);
            $this->entityManager->flush();

            $this->addFlash('success', 'Промоцията беше създадена успешно.');
            return $this->redirectToRoute('admin_property_show', ['id' => $property->getId()]);
        }

        return $this->render('admin/promotion/new.html.twig', [
            'form' => $form->createView(),
            'property' => $property,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_promotion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Promotion $promotion): Response
    {
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Промоцията беше обновена успешно.');
            return $this->redirectToRoute('admin_property_show', ['id' => $promotion->getProperty()->getId()]);
        }

        return $this->render('admin/promotion/edit.html.twig', [
            'form' => $form->createView(),
            'promotion' => $promotion,
        ]);
    }

    #[Route('/{id}', name: 'admin_promotion_delete', methods: ['POST'])]
    public function delete(Request $request, Promotion $promotion): Response
    {
        if ($this->isCsrfTokenValid('delete'.$promotion->getId(), $request->request->get('_token'))) {
            $propertyId = $promotion->getProperty()->getId();
            
            $this->entityManager->remove($promotion);
            $this->entityManager->flush();

            $this->addFlash('success', 'Промоцията беше изтрита успешно.');
            return $this->redirectToRoute('admin_property_show', ['id' => $propertyId]);
        }

        return $this->redirectToRoute('admin_promotion_index');
    }
} 