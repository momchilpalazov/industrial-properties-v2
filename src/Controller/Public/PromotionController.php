<?php

namespace App\Controller\Public;

use App\Form\PromotionFilterType;
use App\Repository\PromotionRepository;
use App\Repository\PropertyTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class PromotionController extends AbstractController
{
    private PromotionRepository $promotionRepository;
    private PropertyTypeRepository $propertyTypeRepository;
    private PaginatorInterface $paginator;

    public function __construct(
        PromotionRepository $promotionRepository,
        PropertyTypeRepository $propertyTypeRepository,
        PaginatorInterface $paginator
    ) {
        $this->promotionRepository = $promotionRepository;
        $this->propertyTypeRepository = $propertyTypeRepository;
        $this->paginator = $paginator;
    }

    #[Route('/promotions', name: 'app_promotions', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Създаваме формата за филтриране
        $form = $this->createForm(PromotionFilterType::class);
        $form->handleRequest($request);

        // Вземаме само активните и платени промоции
        $promotionsQuery = $this->promotionRepository->createQueryBuilder('p')
            ->andWhere('p.isPaid = :isPaid')
            ->andWhere('p.startDate <= :now')
            ->andWhere('p.endDate >= :now')
            ->setParameter('isPaid', true)
            ->setParameter('now', new \DateTime())
            ->orderBy('p.startDate', 'DESC')
            ->getQuery();
            
        // Пагинация на резултатите
        $properties = $this->paginator->paginate(
            $promotionsQuery,
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('promotion/index.html.twig', [
            'properties' => $properties,
            'form' => $form->createView()
        ]);
    }
} 