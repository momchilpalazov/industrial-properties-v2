<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/property', name: 'app_property_')]
class PropertyController extends AbstractController
{
    private const PROPERTY_TYPES = [
        'Индустриален парцел' => 'industrial_land',
        'Индустриална сграда' => 'industrial_building',
        'Логистична база' => 'logistics_base',
        'Производствена база' => 'production_facility',
        'Склад' => 'warehouse',
        'Офис сграда' => 'office_building'
    ];

    private PropertyRepository $propertyRepository;
    private PaginatorInterface $paginator;

    public function __construct(
        PropertyRepository $propertyRepository,
        PaginatorInterface $paginator
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->paginator = $paginator;
    }

    #[Route('', name: 'index')]
    public function index(Request $request): Response
    {
        $queryBuilder = $this->propertyRepository->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC');

        // Прилагане на филтрите
        if ($type = $request->query->get('type')) {
            $queryBuilder->andWhere('p.type = :type')
                ->setParameter('type', $type);
        }

        if ($minPrice = $request->query->get('min_price')) {
            $queryBuilder->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice = $request->query->get('max_price')) {
            $queryBuilder->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        if ($minArea = $request->query->get('min_area')) {
            $queryBuilder->andWhere('p.area >= :minArea')
                ->setParameter('minArea', $minArea);
        }

        if ($maxArea = $request->query->get('max_area')) {
            $queryBuilder->andWhere('p.area <= :maxArea')
                ->setParameter('maxArea', $maxArea);
        }

        if ($location = $request->query->get('location')) {
            $queryBuilder->andWhere('p.location LIKE :location')
                ->setParameter('location', '%' . $location . '%');
        }

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            9 // брой имоти на страница
        );

        return $this->render('property/index.html.twig', [
            'properties' => $pagination,
            'types' => self::PROPERTY_TYPES,
            'filters' => [
                'type' => $request->query->get('type'),
                'min_price' => $request->query->get('min_price'),
                'max_price' => $request->query->get('max_price'),
                'min_area' => $request->query->get('min_area'),
                'max_area' => $request->query->get('max_area'),
                'location' => $request->query->get('location'),
            ]
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $property = $this->propertyRepository->find($id);

        if (!$property) {
            throw $this->createNotFoundException('Property not found');
        }

        return $this->render('property/show.html.twig', [
            'property' => $property
        ]);
    }
} 