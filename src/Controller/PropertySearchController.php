<?php

namespace App\Controller;

use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class PropertySearchController extends AbstractController
{
    #[Route('/properties/search', name: 'api_properties_search', methods: ['GET'])]
    public function search(Request $request, PropertyRepository $propertyRepository): JsonResponse
    {
        $query = $request->query->get('q');
        $locale = $request->getLocale();
        
        if (empty($query)) {
            return new JsonResponse([]);
        }

        $properties = $propertyRepository->searchByQuery($query);
        
        $results = array_map(function($property) use ($locale) {
            return [
                'id' => $property->getId(),
                'title' => $locale === 'bg' ? $property->getTitle() : $property->getTitleEn(),
                'location' => $property->getLocation(),
                'type' => $property->getType(),
                'area' => $property->getArea(),
                'price' => $property->getPrice(),
                'url' => $this->generateUrl('app_property_show', [
                    '_locale' => $locale,
                    'id' => $property->getId()
                ])
            ];
        }, $properties);

        return new JsonResponse($results);
    }
} 