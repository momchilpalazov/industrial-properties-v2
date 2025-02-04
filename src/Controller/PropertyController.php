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

class PropertyController extends AbstractController
{
    public function __construct(
        private PropertyRepository $propertyRepository,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/properties', name: 'app_property_index')]
    public function index(Request $request): Response
    {
        $filters = [
            'type' => $request->query->get('type'),
            'min_price' => $request->query->get('min_price'),
            'max_price' => $request->query->get('max_price'),
            'min_area' => $request->query->get('min_area'),
            'max_area' => $request->query->get('max_area'),
            'location' => $request->query->get('location'),
        ];

        $properties = $this->propertyRepository->findByFilters($filters);

        $pagination = $this->paginator->paginate(
            $properties,
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
    public function show(int $id): Response
    {
        $property = $this->propertyRepository->find($id);

        if (!$property) {
            throw $this->createNotFoundException('Имотът не е намерен');
        }

        return $this->render('property/show.html.twig', [
            'property' => $property,
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
        $properties = $this->propertyRepository->findFeatured();

        return $this->render('property/featured.html.twig', [
            'properties' => $properties,
        ]);
    }

    #[Route('/properties/search', name: 'app_property_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $filters = [
            'type' => $request->query->get('type'),
            'min_price' => $request->query->get('min_price'),
            'max_price' => $request->query->get('max_price'),
            'min_area' => $request->query->get('min_area'),
            'max_area' => $request->query->get('max_area'),
            'location' => $request->query->get('location'),
            'sort' => $request->query->get('sort'),
        ];

        $properties = $this->propertyRepository->findByFilters($filters);

        if ($request->isXmlHttpRequest()) {
            return $this->render('property/_list.html.twig', [
                'properties' => $properties,
            ]);
        }

        return $this->redirectToRoute('app_property_index', $filters);
    }
} 