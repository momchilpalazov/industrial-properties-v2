<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PropertyService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class PropertyController extends AbstractController
{
    private PropertyService $propertyService;

    public function __construct(
        Environment $twig,
        PropertyService $propertyService
    ) {
        parent::__construct($twig);
        $this->propertyService = $propertyService;
    }

    public function index(Request $request): Response
    {
        $page = (int) $request->query->get('page', 1);
        $type = $request->query->get('type');
        $minArea = (float) $request->query->get('min_area');
        $maxArea = (float) $request->query->get('max_area');
        $minPrice = (float) $request->query->get('min_price');
        $maxPrice = (float) $request->query->get('max_price');

        $criteria = array_filter([
            'type' => $type,
            'min_area' => $minArea,
            'max_area' => $maxArea,
            'min_price' => $minPrice,
            'max_price' => $maxPrice
        ]);

        $properties = $this->propertyService->searchProperties($criteria);
        $totalProperties = count($properties);
        $itemsPerPage = 12;
        $totalPages = ceil($totalProperties / $itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        $currentPageProperties = array_slice($properties, $offset, $itemsPerPage);

        return $this->render('property/index.html.twig', [
            'properties' => $currentPageProperties,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_properties' => $totalProperties,
            'criteria' => $criteria,
            'current_language' => $request->getLocale()
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $property = $this->propertyService->getProperty($id);
        
        if (!$property) {
            // TODO: Implement proper 404 handling
            return new Response('Property not found', Response::HTTP_NOT_FOUND);
        }

        $similarProperties = $this->propertyService->getSimilarProperties($property, 3);

        return $this->render('property/show.html.twig', [
            'property' => $property,
            'similar_properties' => $similarProperties,
            'current_language' => $request->getLocale()
        ]);
    }

    public function contact(Request $request, int $id): Response
    {
        if (!$request->isMethod('POST')) {
            return $this->json(['error' => 'Method not allowed'], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $property = $this->propertyService->getProperty($id);
        if (!$property) {
            return $this->json(['error' => 'Property not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $result = $this->propertyService->sendPropertyInquiry($property, $data);

        if ($result) {
            return $this->json(['message' => 'Inquiry sent successfully']);
        }

        return $this->json(
            ['error' => 'Failed to send inquiry'],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
} 