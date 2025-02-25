<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Form\PropertyFilterType;
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
        $form = $this->createForm(PropertyFilterType::class);
        $form->handleRequest($request);

        $queryBuilder = $this->propertyRepository->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC');

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();

            if (!empty($filters['type'])) {
                $queryBuilder->andWhere('p.type = :type')
                    ->setParameter('type', $filters['type']);
            }

            if (!empty($filters['min_price'])) {
                $queryBuilder->andWhere('p.price >= :minPrice')
                    ->setParameter('minPrice', $filters['min_price']);
            }

            if (!empty($filters['max_price'])) {
                $queryBuilder->andWhere('p.price <= :maxPrice')
                    ->setParameter('maxPrice', $filters['max_price']);
            }

            if (!empty($filters['min_area'])) {
                $queryBuilder->andWhere('p.area >= :minArea')
                    ->setParameter('minArea', $filters['min_area']);
            }

            if (!empty($filters['max_area'])) {
                $queryBuilder->andWhere('p.area <= :maxArea')
                    ->setParameter('maxArea', $filters['max_area']);
            }

            if (!empty($filters['location'])) {
                $queryBuilder->andWhere('p.locationBg LIKE :location OR p.locationEn LIKE :location')
                    ->setParameter('location', '%' . $filters['location'] . '%');
            }
        }

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('property/index.html.twig', [
            'properties' => $pagination,
            'types' => self::PROPERTY_TYPES,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $property = $this->propertyRepository->find($id);

        if (!$property || !$property->isActive()) {
            throw $this->createNotFoundException('Имотът не е намерен');
        }

        return $this->render('property/show.html.twig', [
            'property' => $property
        ]);
    }
} 