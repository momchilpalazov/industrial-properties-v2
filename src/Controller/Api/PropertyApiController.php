<?php

namespace App\Controller\Api;

use App\Service\AiDataService;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/properties', name: 'api_properties_')]
class PropertyApiController extends AbstractController
{
    public function __construct(
        private AiDataService $aiDataService,
        private PropertyRepository $propertyRepository
    ) {}

    /**
     * Get all properties in AI-friendly format
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 50)));
        $locale = $request->query->get('locale', 'en');

        try {
            $data = $this->aiDataService->getAllPropertiesForAi($page, $limit, $locale);
            
            return new JsonResponse([
                'success' => true,
                'data' => $data,
                'api_info' => [
                    'version' => '1.0',
                    'endpoint' => 'properties.list',
                    'documentation' => 'https://your-domain.com/api/docs',
                    'rate_limit' => '1000 requests per hour',
                    'supported_locales' => ['bg', 'en', 'de', 'ru']
                ]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Failed to fetch properties',
                    'code' => 'FETCH_ERROR',
                    'details' => $this->getParameter('kernel.debug') ? $e->getMessage() : null
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a single property by ID in AI-friendly format
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, Request $request): JsonResponse
    {
        $locale = $request->query->get('locale', 'en');

        try {
            $property = $this->propertyRepository->find($id);
            
            if (!$property || !$property->isActive()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => [
                        'message' => 'Property not found or not active',
                        'code' => 'NOT_FOUND'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $data = $this->aiDataService->transformPropertyForAi($property, $locale);
            
            return new JsonResponse([
                'success' => true,
                'data' => $data,
                'api_info' => [
                    'version' => '1.0',
                    'endpoint' => 'properties.show',
                    'property_id' => $id,
                    'locale' => $locale
                ]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Failed to fetch property',
                    'code' => 'FETCH_ERROR',
                    'details' => $this->getParameter('kernel.debug') ? $e->getMessage() : null
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search properties with AI-enhanced filtering
     */
    #[Route('/search', name: 'search', methods: ['GET', 'POST'])]
    public function search(Request $request): JsonResponse
    {
        $locale = $request->query->get('locale') ?: $request->request->get('locale', 'en');
        
        // Get search criteria from query parameters or JSON body
        $criteria = [];
        
        if ($request->isMethod('GET')) {
            $criteria = [
                'type' => $request->query->get('type'),
                'min_price' => $request->query->get('min_price'),
                'max_price' => $request->query->get('max_price'),
                'min_area' => $request->query->get('min_area'),
                'max_area' => $request->query->get('max_area'),
                'location' => $request->query->get('location'),
                'status' => $request->query->get('status'),
                'category' => $request->query->get('category'),
            ];
        } else {
            $jsonData = json_decode($request->getContent(), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                $criteria = $jsonData;
            }
        }

        // Remove empty criteria
        $criteria = array_filter($criteria, fn($value) => $value !== null && $value !== '');

        try {
            $data = $this->aiDataService->searchPropertiesForAi($criteria, $locale);
            
            return new JsonResponse([
                'success' => true,
                'data' => $data,
                'search_info' => [
                    'method' => $request->getMethod(),
                    'criteria_count' => count($criteria),
                    'ai_enhanced' => true,
                    'semantic_search' => false, // Will be true when we implement semantic search
                ]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Search failed',
                    'code' => 'SEARCH_ERROR',
                    'details' => $this->getParameter('kernel.debug') ? $e->getMessage() : null
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get similar properties using AI recommendations
     */
    #[Route('/{id}/similar', name: 'similar', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function similar(int $id, Request $request): JsonResponse
    {
        $locale = $request->query->get('locale', 'en');
        $limit = min(20, max(1, (int) $request->query->get('limit', 6)));

        try {
            $property = $this->propertyRepository->find($id);
            
            if (!$property || !$property->isActive()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => [
                        'message' => 'Property not found or not active',
                        'code' => 'NOT_FOUND'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            // Use existing property characteristics for similarity search
            $criteria = [
                'type' => $property->getType()?->getName(),
                'min_price' => $property->getPrice() ? $property->getPrice() * 0.7 : null,
                'max_price' => $property->getPrice() ? $property->getPrice() * 1.3 : null,
                'min_area' => $property->getArea() ? $property->getArea() * 0.8 : null,
                'max_area' => $property->getArea() ? $property->getArea() * 1.2 : null,
            ];

            // Remove the current property from results and get similar ones
            $searchResults = $this->aiDataService->searchPropertiesForAi($criteria, $locale);
            
            // Filter out the current property and limit results
            $similarProperties = array_filter(
                $searchResults['results'], 
                fn($prop) => $prop['id'] !== $id
            );
            $similarProperties = array_slice($similarProperties, 0, $limit);
            
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'original_property_id' => $id,
                    'similar_properties' => $similarProperties,
                    'similarity_criteria' => $criteria,
                    'total_found' => count($similarProperties),
                ],
                'recommendation_info' => [
                    'algorithm' => 'property_characteristics_matching',
                    'ai_enhanced' => true,
                    'version' => '1.0'
                ]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Failed to find similar properties',
                    'code' => 'RECOMMENDATION_ERROR',
                    'details' => $this->getParameter('kernel.debug') ? $e->getMessage() : null
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get API schema/documentation
     */
    #[Route('/schema', name: 'schema', methods: ['GET'])]
    public function schema(): JsonResponse
    {
        return new JsonResponse([
            'api_version' => '1.0',
            'title' => 'Industrial Properties Europe - AI API',
            'description' => 'AI-enhanced API for industrial property data',
            'base_url' => $this->generateUrl('api_properties_list', [], true),
            'endpoints' => [
                'list_properties' => [
                    'url' => '/api/properties',
                    'method' => 'GET',
                    'parameters' => [
                        'page' => 'Page number (default: 1)',
                        'limit' => 'Items per page (max: 100, default: 50)',
                        'locale' => 'Language code (bg, en, de, ru)'
                    ],
                    'description' => 'Get paginated list of all active properties'
                ],
                'get_property' => [
                    'url' => '/api/properties/{id}',
                    'method' => 'GET',
                    'parameters' => [
                        'id' => 'Property ID (required)',
                        'locale' => 'Language code (bg, en, de, ru)'
                    ],
                    'description' => 'Get detailed property information'
                ],
                'search_properties' => [
                    'url' => '/api/properties/search',
                    'method' => 'GET|POST',
                    'parameters' => [
                        'type' => 'Property type filter',
                        'min_price' => 'Minimum price',
                        'max_price' => 'Maximum price',
                        'min_area' => 'Minimum area (sqm)',
                        'max_area' => 'Maximum area (sqm)',
                        'location' => 'Location search term',
                        'status' => 'Property status',
                        'locale' => 'Language code'
                    ],
                    'description' => 'Search properties with flexible filtering'
                ],
                'similar_properties' => [
                    'url' => '/api/properties/{id}/similar',
                    'method' => 'GET',
                    'parameters' => [
                        'id' => 'Property ID (required)',
                        'limit' => 'Max similar properties (max: 20, default: 6)',
                        'locale' => 'Language code'
                    ],
                    'description' => 'Get AI-recommended similar properties'
                ]
            ],
            'data_format' => [
                'response_structure' => [
                    'success' => 'boolean - Operation success status',
                    'data' => 'object - Response data',
                    'error' => 'object - Error information (if any)',
                    'api_info' => 'object - API metadata'
                ],
                'property_structure' => [
                    'id' => 'integer - Unique property ID',
                    'url' => 'string - Property detail URL',
                    'content' => 'object - Multilingual content',
                    'characteristics' => 'object - Property features',
                    'location_data' => 'object - Geographic information',
                    'media' => 'object - Images and media',
                    'ai_tags' => 'array - AI-generated semantic tags',
                    'seo_data' => 'object - SEO metadata',
                    'timestamps' => 'object - Creation/update dates',
                    'recommendation_context' => 'object - AI recommendation data'
                ]
            ],
            'rate_limits' => [
                'requests_per_hour' => 1000,
                'requests_per_minute' => 100,
                'burst_limit' => 20
            ],
            'authentication' => [
                'required' => false,
                'future_plans' => 'API key authentication for higher limits'
            ],
            'ai_features' => [
                'structured_data' => 'Properties formatted for AI consumption',
                'semantic_tags' => 'AI-generated content categorization',
                'similarity_matching' => 'Property recommendation algorithm',
                'multilingual_support' => 'Content in 4 languages',
                'machine_readable' => 'Schema.org compatible structure'
            ]
        ], Response::HTTP_OK);
    }
}
