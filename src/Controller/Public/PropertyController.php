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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
    private ParameterBagInterface $parameterBag;

    public function __construct(
        PropertyRepository $propertyRepository,
        PaginatorInterface $paginator,
        ParameterBagInterface $parameterBag
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->paginator = $paginator;
        $this->parameterBag = $parameterBag;
    }

    #[Route('', name: 'index')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(PropertyFilterType::class);
        $form->handleRequest($request);

        $queryBuilder = $this->propertyRepository->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->where('p.isActive = :active')
            ->andWhere('c.name = :categoryName')
            ->setParameter('active', true)
            ->setParameter('categoryName', 'Продажба')
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

            if (!empty($filters['sort'])) {
                switch ($filters['sort']) {
                    case 'newest':
                        $queryBuilder->orderBy('p.createdAt', 'DESC');
                        break;
                    case 'price_asc':
                        $queryBuilder->orderBy('p.price', 'ASC');
                        break;
                    case 'price_desc':
                        $queryBuilder->orderBy('p.price', 'DESC');
                        break;
                    case 'area_asc':
                        $queryBuilder->orderBy('p.area', 'ASC');
                        break;
                    case 'area_desc':
                        $queryBuilder->orderBy('p.area', 'DESC');
                        break;
                }
            }
        }

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            9,
            [
                'defaultSortFieldName' => 'p.createdAt',
                'defaultSortDirection' => 'desc',
                'sortFieldParameterName' => 'sort',
                'sortDirectionParameterName' => 'direction'
            ]
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

        // Задаване на reCAPTCHA ключ, който да се използва в шаблона
        try {
            // Опитваме се да вземем ключ от параметрите
            $recaptchaSiteKey = $this->getParameter('recaptcha.site_key');
        } catch (\Exception $e) {
            // Ако възникне грешка, използваме тестов ключ от документацията на Google
            // Този ключ работи само за localhost
            $recaptchaSiteKey = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
        }

        return $this->render('property/show.html.twig', [
            'property' => $property,
            'recaptcha_site_key' => $recaptchaSiteKey
        ]);
    }
} 