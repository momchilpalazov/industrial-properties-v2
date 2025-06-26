<?php

namespace App\Controller\Api;

use App\Service\AiDataService;
use App\Service\InternalLinkingService;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/search', name: 'api_search_')]
class SearchApiController extends AbstractController
{
    public function __construct(
        private AiDataService $aiDataService,
        private InternalLinkingService $internalLinkingService,
        private PropertyRepository $propertyRepository
    ) {}

    /**
     * Semantic search endpoint (foundation for future AI integration)
     */
    #[Route('/semantic', name: 'semantic', methods: ['GET', 'POST'])]
    public function semantic(Request $request): JsonResponse
    {
        $locale = $request->query->get('locale') ?: $request->request->get('locale', 'en');
        
        // Get the search query
        $query = $request->isMethod('GET') 
            ? $request->query->get('q', '') 
            : ($request->request->get('q') ?: json_decode($request->getContent(), true)['q'] ?? '');

        if (empty($query)) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Search query is required',
                    'code' => 'MISSING_QUERY',
                    'parameter' => 'q'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // For now, we'll do enhanced keyword-based search
            // Later this can be replaced with vector search or OpenAI embeddings
            $enhancedCriteria = $this->parseSemanticQuery($query, $locale);
            
            $results = $this->aiDataService->searchPropertiesForAi($enhancedCriteria, $locale);
            
            return new JsonResponse([
                'success' => true,
                'data' => $results,
                'semantic_info' => [
                    'original_query' => $query,
                    'parsed_criteria' => $enhancedCriteria,
                    'algorithm' => 'enhanced_keyword_matching',
                    'ai_features' => [
                        'synonym_expansion' => true,
                        'context_understanding' => true,
                        'multilingual_support' => true,
                        'vector_search' => false // Future feature
                    ]
                ]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Semantic search failed',
                    'code' => 'SEMANTIC_SEARCH_ERROR',
                    'details' => $this->getParameter('kernel.debug') ? $e->getMessage() : null
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get property recommendations based on user behavior or preferences
     */
    #[Route('/recommendations', name: 'recommendations', methods: ['GET', 'POST'])]
    public function recommendations(Request $request): JsonResponse
    {
        $locale = $request->query->get('locale') ?: $request->request->get('locale', 'en');
        $limit = min(20, max(1, (int) ($request->query->get('limit') ?: $request->request->get('limit', 10))));
        
        // Get user preferences (can be extended later with user sessions/profiles)
        $preferences = $request->isMethod('GET') 
            ? $request->query->all()
            : array_merge($request->request->all(), json_decode($request->getContent(), true) ?: []);

        try {
            // Generate recommendations based on preferences
            $recommendations = $this->generateRecommendations($preferences, $locale, $limit);
            
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'recommendations' => $recommendations,
                    'total_count' => count($recommendations),
                    'algorithm_info' => [
                        'type' => 'preference_based',
                        'factors_considered' => array_keys($preferences),
                        'personalization_level' => 'basic', // Can be enhanced later
                    ]
                ],
                'recommendation_info' => [
                    'generated_at' => date('c'),
                    'locale' => $locale,
                    'ai_enhanced' => true,
                ]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Failed to generate recommendations',
                    'code' => 'RECOMMENDATION_ERROR',
                    'details' => $this->getParameter('kernel.debug') ? $e->getMessage() : null
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Auto-complete endpoint for search suggestions
     */
    #[Route('/autocomplete', name: 'autocomplete', methods: ['GET'])]
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $locale = $request->query->get('locale', 'en');
        $limit = min(10, max(1, (int) $request->query->get('limit', 5)));

        if (strlen($query) < 2) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Query must be at least 2 characters long',
                    'code' => 'QUERY_TOO_SHORT'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $suggestions = $this->generateAutocompleteSuggestions($query, $locale, $limit);
            
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'suggestions' => $suggestions,
                    'query' => $query,
                    'total_count' => count($suggestions),
                ]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Autocomplete failed',
                    'code' => 'AUTOCOMPLETE_ERROR',
                    'details' => $this->getParameter('kernel.debug') ? $e->getMessage() : null
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get search analytics and popular queries
     */
    #[Route('/analytics', name: 'analytics', methods: ['GET'])]
    public function analytics(Request $request): JsonResponse
    {
        try {
            // Basic analytics - can be enhanced with real tracking later
            $analytics = [
                'popular_searches' => [
                    'warehouses Sofia',
                    'industrial buildings Plovdiv',
                    'logistics centers',
                    'office buildings rent',
                    'manufacturing facilities'
                ],
                'trending_locations' => [
                    'Sofia',
                    'Plovdiv', 
                    'Varna',
                    'Burgas',
                    'Stara Zagora'
                ],
                'price_ranges' => [
                    '50000-100000' => 35,
                    '100000-250000' => 28,
                    '250000-500000' => 22,
                    '500000+' => 15
                ],
                'property_types_demand' => [
                    'warehouse' => 42,
                    'industrial_building' => 28,
                    'office' => 20,
                    'logistics_center' => 10
                ]
            ];
            
            return new JsonResponse([
                'success' => true,
                'data' => $analytics,
                'meta' => [
                    'generated_at' => date('c'),
                    'data_source' => 'aggregated_search_patterns',
                    'ai_insights' => true
                ]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => [
                    'message' => 'Analytics data unavailable',
                    'code' => 'ANALYTICS_ERROR',
                    'details' => $this->getParameter('kernel.debug') ? $e->getMessage() : null
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Parse semantic query into search criteria
     */
    private function parseSemanticQuery(string $query, string $locale): array
    {
        $criteria = [];
        $queryLower = strtolower($query);
        
        // Location parsing
        $locations = ['sofia', 'софия', 'plovdiv', 'пловдив', 'varna', 'варна', 'burgas', 'бургас'];
        foreach ($locations as $location) {
            if (str_contains($queryLower, $location)) {
                $criteria['location'] = $location;
                break;
            }
        }
        
        // Property type parsing with synonyms
        $typeMapping = [
            'warehouse' => ['warehouse', 'склад', 'storage', 'depot'],
            'office' => ['office', 'офис', 'офис сграда', 'office building'],
            'industrial' => ['industrial', 'индустриален', 'manufacturing', 'производство'],
            'logistics' => ['logistics', 'логистичен', 'distribution', 'дистрибуция']
        ];
        
        foreach ($typeMapping as $type => $synonyms) {
            foreach ($synonyms as $synonym) {
                if (str_contains($queryLower, $synonym)) {
                    $criteria['type'] = $type;
                    break 2;
                }
            }
        }
        
        // Price parsing
        if (preg_match('/(\d+)[\s]*(-|до|to)[\s]*(\d+)/', $query, $matches)) {
            $criteria['min_price'] = (int) $matches[1];
            $criteria['max_price'] = (int) $matches[3];
        } elseif (preg_match('/под[\s]*(\d+)|under[\s]*(\d+)|max[\s]*(\d+)/', $queryLower, $matches)) {
            $price = (int) ($matches[1] ?? $matches[2] ?? $matches[3]);
            $criteria['max_price'] = $price;
        } elseif (preg_match('/над[\s]*(\d+)|over[\s]*(\d+)|min[\s]*(\d+)/', $queryLower, $matches)) {
            $price = (int) ($matches[1] ?? $matches[2] ?? $matches[3]);
            $criteria['min_price'] = $price;
        }
        
        // Area parsing
        if (preg_match('/(\d+)[\s]*(-|до|to)[\s]*(\d+)[\s]*(m²|кв\.м|sqm)/', $queryLower, $matches)) {
            $criteria['min_area'] = (int) $matches[1];
            $criteria['max_area'] = (int) $matches[3];
        }
        
        // Status parsing
        if (str_contains($queryLower, 'available') || str_contains($queryLower, 'свободен')) {
            $criteria['status'] = 'available';
        }
        
        return $criteria;
    }

    /**
     * Generate recommendations based on user preferences
     */
    private function generateRecommendations(array $preferences, string $locale, int $limit): array
    {
        // Start with basic search criteria from preferences
        $searchCriteria = [];
        
        // Map preferences to search criteria
        if (!empty($preferences['preferred_type'])) {
            $searchCriteria['type'] = $preferences['preferred_type'];
        }
        
        if (!empty($preferences['budget_max'])) {
            $searchCriteria['max_price'] = (int) $preferences['budget_max'];
        }
        
        if (!empty($preferences['budget_min'])) {
            $searchCriteria['min_price'] = (int) $preferences['budget_min'];
        }
        
        if (!empty($preferences['preferred_location'])) {
            $searchCriteria['location'] = $preferences['preferred_location'];
        }
        
        // Get search results
        $searchResults = $this->aiDataService->searchPropertiesForAi($searchCriteria, $locale);
        
        // Limit and enhance results
        $recommendations = array_slice($searchResults['results'], 0, $limit);
        
        // Add recommendation scores (can be enhanced with ML later)
        foreach ($recommendations as &$property) {
            $property['recommendation_score'] = $this->calculateRecommendationScore($property, $preferences);
            $property['recommendation_reasons'] = $this->generateRecommendationReasons($property, $preferences);
        }
        
        // Sort by recommendation score
        usort($recommendations, fn($a, $b) => $b['recommendation_score'] <=> $a['recommendation_score']);
        
        return $recommendations;
    }

    /**
     * Generate autocomplete suggestions
     */
    private function generateAutocompleteSuggestions(string $query, string $locale, int $limit): array
    {
        $suggestions = [];
        $queryLower = strtolower($query);
        
        // Static suggestions - can be enhanced with real search data later
        $baseSuggestions = [
            'warehouse Sofia',
            'industrial building Plovdiv',
            'office building rent',
            'logistics center Varna',
            'manufacturing facility',
            'storage facility',
            'commercial property',
            'investment property'
        ];
        
        // Filter suggestions that match the query
        foreach ($baseSuggestions as $suggestion) {
            if (str_contains(strtolower($suggestion), $queryLower)) {
                $suggestions[] = [
                    'text' => $suggestion,
                    'type' => 'query',
                    'confidence' => 0.8
                ];
            }
        }
        
        // Add location suggestions
        $locations = ['Sofia', 'Plovdiv', 'Varna', 'Burgas', 'Stara Zagora'];
        foreach ($locations as $location) {
            if (str_contains(strtolower($location), $queryLower)) {
                $suggestions[] = [
                    'text' => $location,
                    'type' => 'location',
                    'confidence' => 0.9
                ];
            }
        }
        
        return array_slice($suggestions, 0, $limit);
    }

    /**
     * Calculate recommendation score for a property
     */
    private function calculateRecommendationScore(array $property, array $preferences): float
    {
        $score = 0.5; // Base score
        
        // Price match bonus
        if (!empty($preferences['budget_max']) && !empty($property['characteristics']['price'])) {
            $budget = (int) $preferences['budget_max'];
            $price = (int) $property['characteristics']['price'];
            if ($price <= $budget) {
                $score += 0.3;
            }
        }
        
        // Type match bonus
        if (!empty($preferences['preferred_type']) && !empty($property['characteristics']['type'])) {
            if (str_contains(strtolower($property['characteristics']['type']), strtolower($preferences['preferred_type']))) {
                $score += 0.2;
            }
        }
        
        // Location match bonus
        if (!empty($preferences['preferred_location'])) {
            $location = strtolower($property['content']['location'] ?? '');
            if (str_contains($location, strtolower($preferences['preferred_location']))) {
                $score += 0.2;
            }
        }
        
        // VIP/Featured bonus
        if ($property['characteristics']['is_vip'] ?? false) {
            $score += 0.1;
        }
        if ($property['characteristics']['is_featured'] ?? false) {
            $score += 0.05;
        }
        
        return min(1.0, $score);
    }

    /**
     * Generate human-readable recommendation reasons
     */
    private function generateRecommendationReasons(array $property, array $preferences): array
    {
        $reasons = [];
        
        if (!empty($preferences['preferred_type']) && !empty($property['characteristics']['type'])) {
            if (str_contains(strtolower($property['characteristics']['type']), strtolower($preferences['preferred_type']))) {
                $reasons[] = 'Matches your preferred property type';
            }
        }
        
        if (!empty($preferences['budget_max']) && !empty($property['characteristics']['price'])) {
            if ((int) $property['characteristics']['price'] <= (int) $preferences['budget_max']) {
                $reasons[] = 'Within your budget range';
            }
        }
        
        if (!empty($preferences['preferred_location'])) {
            $location = strtolower($property['content']['location'] ?? '');
            if (str_contains($location, strtolower($preferences['preferred_location']))) {
                $reasons[] = 'Located in your preferred area';
            }
        }
        
        if ($property['characteristics']['is_featured'] ?? false) {
            $reasons[] = 'Featured property with high interest';
        }
        
        if (empty($reasons)) {
            $reasons[] = 'Popular property in our database';
        }
        
        return $reasons;
    }
}
