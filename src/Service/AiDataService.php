<?php

namespace App\Service;

use App\Entity\Property;
use App\Repository\PropertyRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AiDataService
{
    public function __construct(
        private PropertyRepository $propertyRepository,
        private UrlGeneratorInterface $urlGenerator,
        private OgImageService $ogImageService
    ) {}

    /**
     * Transform property data for AI consumption with enhanced metadata
     */
    public function transformPropertyForAi(Property $property, string $locale = 'en'): array
    {
        $baseUrl = $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        
        return [
            'id' => $property->getId(),
            'url' => $this->urlGenerator->generate('app_property_show', [
                '_locale' => $locale,
                'id' => $property->getId()
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            
            // Multilingual content
            'content' => [
                'title' => $this->getLocalizedContent($property, 'title', $locale),
                'description' => $this->getLocalizedContent($property, 'description', $locale),
                'location' => $this->getLocalizedContent($property, 'location', $locale),
                'all_languages' => [
                    'bg' => [
                        'title' => $property->getTitleBg(),
                        'description' => $property->getDescriptionBg(),
                        'location' => $property->getLocationBg(),
                    ],
                    'en' => [
                        'title' => $property->getTitleEn(),
                        'description' => $property->getDescriptionEn(),
                        'location' => $property->getLocationEn(),
                    ],
                    'de' => [
                        'title' => $property->getTitleDe(),
                        'description' => $property->getDescriptionDe(),
                        'location' => $property->getLocationDe(),
                    ],
                    'ru' => [
                        'title' => $property->getTitleRu(),
                        'description' => $property->getDescriptionRu(),
                        'location' => $property->getLocationRu(),
                    ],
                ]
            ],
            
            // Property characteristics for AI understanding
            'characteristics' => [
                'type' => $property->getType()?->getName(),
                'category' => $property->getCategory()?->getName(),
                'area' => $property->getArea(),
                'price' => $property->getPrice(),
                'price_per_sqm' => $property->getPricePerSqm(),
                'currency' => 'EUR',
                'status' => $property->getStatus(),
                'year_built' => $property->getYearBuilt(),
                'is_active' => $property->isActive(),
                'is_featured' => $property->isFeatured(),
                'is_vip' => $property->isVip(),
                'vip_expiration' => $property->getVipExpiration()?->format('c'),
                'available_from' => $property->getAvailableFrom()?->format('c'),
                'reference_number' => $property->getReferenceNumber(),
                'address' => $property->getAddress(),
                'sold_at' => $property->getSoldAt()?->format('c'),
            ],
            
            // Geographic data for location-based AI queries
            'location_data' => [
                'coordinates' => [
                    'latitude' => $property->getLatitude(),
                    'longitude' => $property->getLongitude(),
                ],
                'address_components' => [
                    'country' => 'Bulgaria',
                    'region' => $this->extractRegionFromLocation($property),
                    'city' => $this->extractCityFromLocation($property),
                    'neighborhood' => $this->extractNeighborhoodFromLocation($property),
                ]
            ],
            
            // Media for AI image understanding
            'media' => [
                'images' => $this->getPropertyImages($property),
                'og_image' => $this->ogImageService->getPropertyOgImage($property),
                'image_count' => $property->getImages() ? count($property->getImages()) : 0,
            ],
            
            // Semantic tags for AI categorization
            'ai_tags' => $this->generateAiTags($property),
            
            // SEO and structured data
            'seo_data' => [
                'keywords' => $this->generateKeywords($property, $locale),
                'schema_type' => 'RealEstate',
                'canonical_url' => $this->urlGenerator->generate('app_property_show', [
                    '_locale' => $locale,
                    'id' => $property->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL),
            ],
            
            // Timestamps for AI freshness scoring
            'timestamps' => [
                'created_at' => $property->getCreatedAt()?->format('c'),
                'updated_at' => $property->getUpdatedAt()?->format('c'),
                'last_modified' => $property->getUpdatedAt()?->getTimestamp(),
            ],
            
            // AI recommendation context
            'recommendation_context' => [
                'price_per_sqm' => $property->getPrice() && $property->getArea() 
                    ? round($property->getPrice() / $property->getArea(), 2) 
                    : null,
                'investment_type' => $this->determineInvestmentType($property),
                'target_audience' => $this->determineTargetAudience($property),
                'comparable_properties_count' => $this->getComparablePropertiesCount($property),
            ]
        ];
    }

    /**
     * Get all properties transformed for AI with pagination
     */
    public function getAllPropertiesForAi(int $page = 1, int $limit = 50, string $locale = 'en'): array
    {
        $properties = $this->propertyRepository->findAllPublished();
        $total = count($properties);
        
        // Simple pagination
        $offset = ($page - 1) * $limit;
        $paginatedProperties = array_slice($properties, $offset, $limit);
        
        $transformedProperties = [];
        foreach ($paginatedProperties as $property) {
            $transformedProperties[] = $this->transformPropertyForAi($property, $locale);
        }
        
        return [
            'properties' => $transformedProperties,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_previous' => $page > 1,
            ],
            'meta' => [
                'generated_at' => date('c'),
                'api_version' => '1.0',
                'locale' => $locale,
                'total_properties' => $total,
            ]
        ];
    }

    /**
     * Search properties with AI-friendly results
     */
    public function searchPropertiesForAi(array $criteria, string $locale = 'en'): array
    {
        // Build search query
        $qb = $this->propertyRepository->createQueryBuilder('p')
            ->leftJoin('p.type', 'pt')
            ->where('p.isActive = :active')
            ->setParameter('active', true);

        // Apply search criteria
        if (!empty($criteria['type'])) {
            $qb->andWhere('pt.nameBg LIKE :type OR pt.nameEn LIKE :type OR pt.nameDe LIKE :type OR pt.nameRu LIKE :type')
               ->setParameter('type', '%' . $criteria['type'] . '%');
        }

        if (!empty($criteria['min_price'])) {
            $qb->andWhere('p.price >= :min_price')
               ->setParameter('min_price', $criteria['min_price']);
        }

        if (!empty($criteria['max_price'])) {
            $qb->andWhere('p.price <= :max_price')
               ->setParameter('max_price', $criteria['max_price']);
        }

        if (!empty($criteria['min_area'])) {
            $qb->andWhere('p.area >= :min_area')
               ->setParameter('min_area', $criteria['min_area']);
        }

        if (!empty($criteria['max_area'])) {
            $qb->andWhere('p.area <= :max_area')
               ->setParameter('max_area', $criteria['max_area']);
        }

        if (!empty($criteria['location'])) {
            if (is_array($criteria['location'])) {
                // Multiple location variants (e.g., ['София', 'Sofia', 'sofia'])
                $locationConditions = [];
                $locationParams = [];
                foreach ($criteria['location'] as $index => $location) {
                    $locationConditions[] = "p.locationBg LIKE :location{$index} OR p.locationEn LIKE :location{$index} OR p.locationDe LIKE :location{$index} OR p.locationRu LIKE :location{$index}";
                    $locationParams["location{$index}"] = '%' . $location . '%';
                }
                $qb->andWhere('(' . implode(' OR ', $locationConditions) . ')');
                foreach ($locationParams as $param => $value) {
                    $qb->setParameter($param, $value);
                }
                
                // Debug logging
                error_log("Searching with location array: " . json_encode($criteria['location'], JSON_UNESCAPED_UNICODE));
                error_log("Generated location condition: " . implode(' OR ', $locationConditions));
            } else {
                // Single location string
                $qb->andWhere('p.locationBg LIKE :location OR p.locationEn LIKE :location OR p.locationDe LIKE :location OR p.locationRu LIKE :location')
                   ->setParameter('location', '%' . $criteria['location'] . '%');
                
                // Debug logging
                error_log("Searching with location string: " . $criteria['location']);
            }
        }

        $properties = $qb->getQuery()->getResult();
        
        // Debug logging
        error_log("Search query returned " . count($properties) . " properties");

        $transformedProperties = [];
        foreach ($properties as $property) {
            $transformedProperties[] = $this->transformPropertyForAi($property, $locale);
        }

        return [
            'results' => $transformedProperties,
            'search_criteria' => $criteria,
            'results_count' => count($transformedProperties),
            'meta' => [
                'generated_at' => date('c'),
                'search_type' => 'ai_enhanced',
                'locale' => $locale,
            ]
        ];
    }

    private function getLocalizedContent(Property $property, string $field, string $locale): string
    {
        return match($locale) {
            'bg' => $this->getPropertyField($property, $field . 'Bg') ?: 
                    $this->getPropertyField($property, $field . 'En'),
            'de' => $this->getPropertyField($property, $field . 'De') ?: 
                    $this->getPropertyField($property, $field . 'En'),
            'ru' => $this->getPropertyField($property, $field . 'Ru') ?: 
                    $this->getPropertyField($property, $field . 'En'),
            default => $this->getPropertyField($property, $field . 'En') ?: 
                       $this->getPropertyField($property, $field . 'Bg')
        };
    }

    private function getPropertyField(Property $property, string $field): ?string
    {
        $method = 'get' . ucfirst($field);
        return method_exists($property, $method) ? $property->$method() : null;
    }

    private function getPropertyImages(Property $property): array
    {
        $images = [];
        if ($property->getImages()) {
            foreach ($property->getImages() as $image) {
                $images[] = [
                    'url' => $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL) . 
                             'uploads/images/properties/' . $property->getId() . '/' . $image->getFilename(),
                    'filename' => $image->getFilename(),
                    'alt' => $this->ogImageService->generatePropertyImageAlt($property, 'en'),
                ];
            }
        }
        return $images;
    }

    private function generateAiTags(Property $property): array
    {
        $tags = [];
        
        // Industry tags
        if (str_contains(strtolower($property->getType() ?? ''), 'склад')) {
            $tags[] = 'warehouse';
            $tags[] = 'logistics';
        }
        
        if (str_contains(strtolower($property->getType() ?? ''), 'производств')) {
            $tags[] = 'manufacturing';
            $tags[] = 'industrial';
        }
        
        if (str_contains(strtolower($property->getType() ?? ''), 'офис')) {
            $tags[] = 'office';
            $tags[] = 'commercial';
        }
        
        // Size tags
        $area = $property->getArea();
        if ($area) {
            if ($area < 500) $tags[] = 'small_property';
            elseif ($area < 2000) $tags[] = 'medium_property';
            else $tags[] = 'large_property';
        }
        
        // Price tags
        $price = $property->getPrice();
        if ($price) {
            if ($price < 100000) $tags[] = 'budget_friendly';
            elseif ($price < 500000) $tags[] = 'mid_range';
            else $tags[] = 'premium';
        }
        
        // Feature tags - using available fields
        if ($property->isFeatured()) $tags[] = 'featured_property';
        if ($property->isVip()) $tags[] = 'vip_property';
        if ($property->getStatus() === Property::STATUS_AVAILABLE) $tags[] = 'available_now';
        
        // Location-based tags
        $location = strtolower($property->getLocationEn() ?: $property->getLocationBg() ?: '');
        if (str_contains($location, 'sofia')) $tags[] = 'sofia_region';
        if (str_contains($location, 'plovdiv')) $tags[] = 'plovdiv_region';
        if (str_contains($location, 'varna')) $tags[] = 'varna_region';
        if (str_contains($location, 'center') || str_contains($location, 'център')) $tags[] = 'city_center';
        
        return array_unique($tags);
    }

    private function generateKeywords(Property $property, string $locale): array
    {
        $keywords = [];
        
        // Basic keywords
        $keywords[] = $this->getLocalizedContent($property, 'location', $locale);
        $keywords[] = $property->getType();
        
        // Industry keywords
        $keywords = array_merge($keywords, [
            'industrial property',
            'commercial real estate',
            'Bulgaria properties',
            'investment opportunity'
        ]);
        
        // Price-based keywords
        if ($property->getPrice()) {
            $keywords[] = number_format($property->getPrice(), 0, '', ' ') . ' EUR';
        }
        
        return array_filter(array_unique($keywords));
    }

    private function extractRegionFromLocation(Property $property): ?string
    {
        $location = $property->getLocationEn() ?: $property->getLocationBg();
        // Simple region extraction logic - can be enhanced
        if (str_contains(strtolower($location ?? ''), 'sofia')) return 'Sofia';
        if (str_contains(strtolower($location ?? ''), 'plovdiv')) return 'Plovdiv';
        if (str_contains(strtolower($location ?? ''), 'varna')) return 'Varna';
        if (str_contains(strtolower($location ?? ''), 'burgas')) return 'Burgas';
        return null;
    }

    private function extractCityFromLocation(Property $property): ?string
    {
        return $this->extractRegionFromLocation($property); // Simplified for now
    }

    private function extractNeighborhoodFromLocation(Property $property): ?string
    {
        $location = $property->getLocationEn() ?: $property->getLocationBg();
        // Extract neighborhood after city name - simplified logic
        return null; // Can be enhanced with more sophisticated parsing
    }

    private function determineInvestmentType(Property $property): string
    {
        $type = strtolower($property->getType() ?? '');
        
        if (str_contains($type, 'склад') || str_contains($type, 'warehouse')) {
            return 'logistics_investment';
        }
        
        if (str_contains($type, 'производств') || str_contains($type, 'manufacturing')) {
            return 'industrial_investment';
        }
        
        if (str_contains($type, 'офис') || str_contains($type, 'office')) {
            return 'commercial_investment';
        }
        
        return 'mixed_use_investment';
    }

    private function determineTargetAudience(Property $property): array
    {
        $audiences = [];
        
        $type = strtolower($property->getType() ?? '');
        $price = $property->getPrice();
        
        if (str_contains($type, 'склад')) {
            $audiences[] = 'logistics_companies';
            $audiences[] = 'e_commerce_businesses';
        }
        
        if (str_contains($type, 'производств')) {
            $audiences[] = 'manufacturers';
            $audiences[] = 'industrial_companies';
        }
        
        if ($price && $price > 500000) {
            $audiences[] = 'institutional_investors';
            $audiences[] = 'real_estate_funds';
        } else {
            $audiences[] = 'small_medium_enterprises';
            $audiences[] = 'individual_investors';
        }
        
        return $audiences;
    }

    private function getComparablePropertiesCount(Property $property): int
    {
        $qb = $this->propertyRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.isActive = :active')
            ->andWhere('p.id != :currentId')
            ->setParameter('active', true)
            ->setParameter('currentId', $property->getId());

        // Similar type
        if ($property->getType()) {
            $qb->andWhere('p.type = :type')
               ->setParameter('type', $property->getType());
        }

        // Similar price range (+/- 30%)
        if ($property->getPrice()) {
            $minPrice = $property->getPrice() * 0.7;
            $maxPrice = $property->getPrice() * 1.3;
            $qb->andWhere('p.price BETWEEN :minPrice AND :maxPrice')
               ->setParameter('minPrice', $minPrice)
               ->setParameter('maxPrice', $maxPrice);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
