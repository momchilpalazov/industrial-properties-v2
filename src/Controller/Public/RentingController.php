<?php

namespace App\Controller\Public;

use App\Entity\PropertyCategory;
use App\Repository\PropertyRepository;
use App\Repository\PropertyTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RentingController extends AbstractController
{
    private PropertyRepository $propertyRepository;
    private PaginatorInterface $paginator;
    private EntityManagerInterface $entityManager;
    private PropertyTypeRepository $propertyTypeRepository;

    public function __construct(
        PropertyRepository $propertyRepository,
        PaginatorInterface $paginator,
        EntityManagerInterface $entityManager,
        PropertyTypeRepository $propertyTypeRepository
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->paginator = $paginator;
        $this->entityManager = $entityManager;
        $this->propertyTypeRepository = $propertyTypeRepository;
    }

    #[Route('/renting', name: 'app_renting')]
    public function index(Request $request): Response
    {
        // Намираме категорията "Под наем"
        $rentCategory = $this->entityManager->getRepository(PropertyCategory::class)
            ->findOneBy(['slug' => 'for-rent']);

        if (!$rentCategory) {
            throw $this->createNotFoundException('Категорията "Под наем" не е намерена.');
        }

        $queryBuilder = $this->propertyRepository->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->andWhere('p.category = :category')
            ->setParameter('active', true)
            ->setParameter('category', $rentCategory)
            ->orderBy('p.createdAt', 'DESC');

        // Филтриране по тип
        if ($request->query->has('type') && $request->query->get('type') !== '') {
            $queryBuilder
                ->andWhere('p.type = :type')
                ->setParameter('type', $request->query->get('type'));
        }

        // Филтриране по цена
        if ($request->query->has('min_price') && $request->query->get('min_price') !== '') {
            $queryBuilder
                ->andWhere('p.price >= :min_price')
                ->setParameter('min_price', $request->query->get('min_price'));
        }

        if ($request->query->has('max_price') && $request->query->get('max_price') !== '') {
            $queryBuilder
                ->andWhere('p.price <= :max_price')
                ->setParameter('max_price', $request->query->get('max_price'));
        }

        // Филтриране по площ
        if ($request->query->has('min_area') && $request->query->get('min_area') !== '') {
            $queryBuilder
                ->andWhere('p.area >= :min_area')
                ->setParameter('min_area', $request->query->get('min_area'));
        }

        if ($request->query->has('max_area') && $request->query->get('max_area') !== '') {
            $queryBuilder
                ->andWhere('p.area <= :max_area')
                ->setParameter('max_area', $request->query->get('max_area'));
        }

        // Филтриране по локация (текстово поле)
        if ($request->query->has('location') && $request->query->get('location') !== '') {
            $location = $request->query->get('location');
            $queryBuilder
                ->andWhere('p.locationBg LIKE :location OR p.locationEn LIKE :location')
                ->setParameter('location', '%' . $location . '%');
        }

        // Сортиране
        if ($request->query->has('sort') && $request->query->get('sort') !== '') {
            $sort = $request->query->get('sort');
            switch ($sort) {
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
                case 'newest':
                    $queryBuilder->orderBy('p.createdAt', 'DESC');
                    break;
                case 'oldest':
                    $queryBuilder->orderBy('p.createdAt', 'ASC');
                    break;
                default:
                    $queryBuilder->orderBy('p.createdAt', 'DESC');
            }
        }

        $properties = $this->paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            9
        );

        // Вземаме всички типове имоти за филтрите
        $propertyTypes = $this->propertyTypeRepository->findBy(['isActive' => true], ['position' => 'ASC']);

        return $this->render('renting/index.html.twig', [
            'properties' => $properties,
            'category' => $rentCategory,
            'filters' => $request->query->all(),
            'property_types' => $propertyTypes,
        ]);
    }
} 