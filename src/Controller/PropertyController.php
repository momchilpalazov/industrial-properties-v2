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

#[Route('/{_locale}')]
class PropertyController extends AbstractController
{
    public function __construct(
        private PropertyRepository $propertyRepository,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/properties', name: 'app_property_index')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(PropertyFilterType::class);
        $form->handleRequest($request);

        $filters = $form->isSubmitted() && $form->isValid() 
            ? $form->getData() 
            : [];

        $queryBuilder = $this->propertyRepository->createQueryBuilderWithFilters($filters);
        
        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('property/index.html.twig', [
            'properties' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/property/{slug}', name: 'app_property_show')]
    public function show(Property $property): Response
    {
        if (!$property->isActive()) {
            throw $this->createNotFoundException('Property not found');
        }

        $similarProperties = $this->propertyRepository->findSimilar($property);

        return $this->render('property/show.html.twig', [
            'property' => $property,
            'similar_properties' => $similarProperties,
        ]);
    }

    #[Route('/featured-properties', name: 'app_property_featured')]
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