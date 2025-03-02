<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Property;
use App\Form\PropertyFilterType;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Connection;
use App\Repository\PropertyViewRepository;

class PropertyController extends AbstractController
{
    public function __construct(
        private PropertyRepository $propertyRepository,
        private PaginatorInterface $paginator,
        private EntityManagerInterface $entityManager,
        private Connection $connection
    ) {}

    #[Route('/properties', name: 'app_property_index')]
    public function index(Request $request): Response
    {
        // Изчистваме кеша на Doctrine
        $this->entityManager->clear();
        
        $response = new Response();
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        $filters = [
            'type' => $request->query->get('type'),
            'min_price' => $request->query->get('min_price'),
            'max_price' => $request->query->get('max_price'),
            'min_area' => $request->query->get('min_area'),
            'max_area' => $request->query->get('max_area'),
            'location' => $request->query->get('location'),
            'status' => $request->query->get('status'),
        ];

        // Използваме QueryBuilder вместо директна SQL заявка
        $qb = $this->propertyRepository->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->setParameter('active', true);

        if (!empty($filters['status'])) {
            $qb->andWhere('p.status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $qb->andWhere('p.type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (!empty($filters['min_price'])) {
            $qb->andWhere('p.price >= :min_price')
               ->setParameter('min_price', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $qb->andWhere('p.price <= :max_price')
               ->setParameter('max_price', $filters['max_price']);
        }

        if (!empty($filters['min_area'])) {
            $qb->andWhere('p.area >= :min_area')
               ->setParameter('min_area', $filters['min_area']);
        }

        if (!empty($filters['max_area'])) {
            $qb->andWhere('p.area <= :max_area')
               ->setParameter('max_area', $filters['max_area']);
        }

        if (!empty($filters['location'])) {
            $qb->andWhere('p.locationBg LIKE :location OR p.locationEn LIKE :location')
               ->setParameter('location', '%' . $filters['location'] . '%');
        }

        $qb->orderBy('p.createdAt', 'DESC');

        $pagination = $this->paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('property/index.html.twig', [
            'properties' => $pagination,
            'filters' => $filters,
            'types' => $this->propertyRepository->getPropertyTypes(),
        ]);
    }

    #[Route('/properties/{id}', name: 'app_property_show', methods: ['GET'])]
    public function show(Property $property, Request $request, PropertyViewRepository $viewRepository): Response
    {
        // Записваме разглеждането
        $viewRepository->addView(
            $property->getId(),
            $request->getClientIp(),
            $request->headers->get('User-Agent')
        );

        return $this->render('property/show.html.twig', [
            'property' => $property
        ]);
    }

    #[Route('/properties/{id}/inquiry', name: 'app_property_inquiry', methods: ['POST'])]
    public function inquiry(Request $request, int $id): Response
    {
        $property = $this->propertyRepository->find($id);

        if (!$property) {
            throw $this->createNotFoundException('Имотът не е намерен');
        }

        // TODO: Обработка на запитването

        $this->addFlash('success', 'Вашето запитване беше изпратено успешно');

        return $this->redirectToRoute('app_property_show', ['id' => $id]);
    }

    #[Route('/properties/featured', name: 'app_property_featured')]
    public function featured(): Response
    {
        // Изчистваме кеша на Doctrine
        $this->entityManager->clear();

        // Използваме QueryBuilder за да сме сигурни, че взимаме актуалните данни
        $properties = $this->propertyRepository->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->andWhere('p.isFeatured = :featured')
            ->setParameter('active', true)
            ->setParameter('featured', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('property/featured.html.twig', [
            'properties' => $properties,
        ]);
    }

    #[Route('/properties/search', name: 'app_property_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        // Изчистваме кеша на Doctrine
        $this->entityManager->clear();

        $filters = [
            'type' => $request->query->get('type'),
            'min_price' => $request->query->get('min_price'),
            'max_price' => $request->query->get('max_price'),
            'min_area' => $request->query->get('min_area'),
            'max_area' => $request->query->get('max_area'),
            'location' => $request->query->get('location'),
            'sort' => $request->query->get('sort'),
        ];

        // Създаваме QueryBuilder
        $qb = $this->propertyRepository->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->setParameter('active', true);

        if (!empty($filters['type'])) {
            $qb->andWhere('p.type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (!empty($filters['min_price'])) {
            $qb->andWhere('p.price >= :min_price')
               ->setParameter('min_price', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $qb->andWhere('p.price <= :max_price')
               ->setParameter('max_price', $filters['max_price']);
        }

        if (!empty($filters['min_area'])) {
            $qb->andWhere('p.area >= :min_area')
               ->setParameter('min_area', $filters['min_area']);
        }

        if (!empty($filters['max_area'])) {
            $qb->andWhere('p.area <= :max_area')
               ->setParameter('max_area', $filters['max_area']);
        }

        if (!empty($filters['location'])) {
            $qb->andWhere('p.locationBg LIKE :location OR p.locationEn LIKE :location')
               ->setParameter('location', '%' . $filters['location'] . '%');
        }

        $qb->orderBy('p.createdAt', 'DESC');

        $properties = $qb->getQuery()->getResult();

        if ($request->isXmlHttpRequest()) {
            return $this->render('property/_list.html.twig', [
                'properties' => $properties,
            ]);
        }

        return $this->redirectToRoute('app_property_index', $filters);
    }
} 