<?php

namespace App\Service;

use App\Entity\Property;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;

class InternalLinkingService
{
    public function __construct(
        private PropertyRepository $propertyRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function generateRelatedProperties(Property $property, int $limit = 6): array
    {
        $qb = $this->propertyRepository->createQueryBuilder('p');
        
        $relatedProperties = $qb
            ->where('p.isActive = :active')
            ->andWhere('p.id != :currentId')
            ->setParameter('active', true)
            ->setParameter('currentId', $property->getId())
            ->orderBy('p.updatedAt', 'DESC')
            ->setMaxResults($limit * 2) // Get more to filter
            ->getQuery()
            ->getResult();

        // Score properties by similarity
        $scoredProperties = [];
        foreach ($relatedProperties as $relatedProperty) {
            $score = $this->calculateSimilarityScore($property, $relatedProperty);
            if ($score > 0) {
                $scoredProperties[] = [
                    'property' => $relatedProperty,
                    'score' => $score
                ];
            }
        }

        // Sort by score and return top results
        usort($scoredProperties, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return array_slice(
            array_column($scoredProperties, 'property'), 
            0, 
            $limit
        );
    }

    public function getSimilarPropertiesByLocation(Property $property, int $limit = 4): array
    {
        $location = $property->getLocationBg() ?: $property->getLocationEn();
        if (!$location) {
            return [];
        }

        // Extract city/region from location
        $locationKeywords = $this->extractLocationKeywords($location);
        
        if (empty($locationKeywords)) {
            return [];
        }

        $qb = $this->propertyRepository->createQueryBuilder('p');
        $qb->where('p.isActive = :active')
           ->andWhere('p.id != :currentId')
           ->setParameter('active', true)
           ->setParameter('currentId', $property->getId());

        // Build OR conditions for location matching
        $orConditions = $qb->expr()->orX();
        foreach ($locationKeywords as $i => $keyword) {
            $orConditions->add($qb->expr()->like('p.locationBg', ':keyword' . $i));
            $orConditions->add($qb->expr()->like('p.locationEn', ':keyword' . $i));
            $qb->setParameter('keyword' . $i, '%' . $keyword . '%');
        }
        
        $qb->andWhere($orConditions)
           ->orderBy('p.updatedAt', 'DESC')
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function getSimilarPropertiesByType(Property $property, int $limit = 4): array
    {
        if (!$property->getType()) {
            return [];
        }

        return $this->propertyRepository->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->andWhere('p.type = :type')
            ->andWhere('p.id != :currentId')
            ->setParameter('active', true)
            ->setParameter('type', $property->getType())
            ->setParameter('currentId', $property->getId())
            ->orderBy('p.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getSimilarPropertiesByPriceRange(Property $property, int $limit = 4): array
    {
        if (!$property->getPrice()) {
            return [];
        }

        $price = $property->getPrice();
        $lowerBound = $price * 0.7; // 30% lower
        $upperBound = $price * 1.3; // 30% higher

        return $this->propertyRepository->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->andWhere('p.price BETWEEN :lowerBound AND :upperBound')
            ->andWhere('p.id != :currentId')
            ->setParameter('active', true)
            ->setParameter('lowerBound', $lowerBound)
            ->setParameter('upperBound', $upperBound)
            ->setParameter('currentId', $property->getId())
            ->orderBy('p.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function createTopicalClusters(): array
    {
        // Group properties by topic/type for better internal linking
        $clusters = [
            'warehouses' => $this->getPropertiesByKeywords(['warehouse', 'склад', 'lager']),
            'manufacturing' => $this->getPropertiesByKeywords(['production', 'manufacturing', 'производство', 'produktion']),
            'logistics' => $this->getPropertiesByKeywords(['logistics', 'distribution', 'логистика', 'logistik']),
            'industrial_land' => $this->getPropertiesByKeywords(['land', 'plot', 'парцел', 'grundstück']),
        ];

        return array_filter($clusters, fn($cluster) => count($cluster) > 0);
    }

    public function generateInternalLinks(Property $property, string $locale = 'en'): array
    {
        $links = [];

        // Related properties by location
        $locationProperties = $this->getSimilarPropertiesByLocation($property, 3);
        if (!empty($locationProperties)) {
            $links['location'] = [
                'title' => $this->getLocationLinkTitle($property, $locale),
                'properties' => $locationProperties
            ];
        }

        // Related properties by type
        $typeProperties = $this->getSimilarPropertiesByType($property, 3);
        if (!empty($typeProperties)) {
            $links['type'] = [
                'title' => $this->getTypeLinkTitle($property, $locale),
                'properties' => $typeProperties
            ];
        }

        // Related properties by price range
        $priceProperties = $this->getSimilarPropertiesByPriceRange($property, 3);
        if (!empty($priceProperties)) {
            $links['price'] = [
                'title' => $this->getPriceLinkTitle($property, $locale),
                'properties' => $priceProperties
            ];
        }

        return $links;
    }

    private function calculateSimilarityScore(Property $property1, Property $property2): float
    {
        $score = 0.0;

        // Location similarity (40% weight)
        if ($this->isLocationSimilar($property1, $property2)) {
            $score += 4.0;
        }

        // Type similarity (30% weight)
        if ($property1->getType() === $property2->getType()) {
            $score += 3.0;
        }

        // Price similarity (20% weight)
        if ($this->isPriceSimilar($property1, $property2)) {
            $score += 2.0;
        }

        // Area similarity (10% weight)
        if ($this->isAreaSimilar($property1, $property2)) {
            $score += 1.0;
        }

        return $score;
    }

    private function isLocationSimilar(Property $property1, Property $property2): bool
    {
        $location1 = strtolower($property1->getLocationBg() ?: $property1->getLocationEn() ?: '');
        $location2 = strtolower($property2->getLocationBg() ?: $property2->getLocationEn() ?: '');

        if (empty($location1) || empty($location2)) {
            return false;
        }

        // Extract main city/region
        $keywords1 = $this->extractLocationKeywords($location1);
        $keywords2 = $this->extractLocationKeywords($location2);

        return !empty(array_intersect($keywords1, $keywords2));
    }

    private function isPriceSimilar(Property $property1, Property $property2): bool
    {
        $price1 = $property1->getPrice();
        $price2 = $property2->getPrice();

        if (!$price1 || !$price2) {
            return false;
        }

        $ratio = max($price1, $price2) / min($price1, $price2);
        return $ratio <= 2.0; // Within 100% price range
    }

    private function isAreaSimilar(Property $property1, Property $property2): bool
    {
        $area1 = $property1->getArea();
        $area2 = $property2->getArea();

        if (!$area1 || !$area2) {
            return false;
        }

        $ratio = max($area1, $area2) / min($area1, $area2);
        return $ratio <= 3.0; // Within 200% area range
    }

    private function extractLocationKeywords(string $location): array
    {
        $location = strtolower(trim($location));
        
        // Remove common words
        $stopWords = ['област', 'region', 'municipality', 'общcommune', 'city', 'град', 'село', 'village'];
        foreach ($stopWords as $stopWord) {
            $location = str_replace($stopWord, '', $location);
        }

        // Split by common separators
        $keywords = preg_split('/[,\-\s]+/', $location);
        
        // Filter out short and empty keywords
        return array_filter($keywords, fn($keyword) => strlen(trim($keyword)) >= 3);
    }

    private function getPropertiesByKeywords(array $keywords): array
    {
        $qb = $this->propertyRepository->createQueryBuilder('p');
        $qb->where('p.isActive = :active')
           ->setParameter('active', true);

        $orConditions = $qb->expr()->orX();
        foreach ($keywords as $i => $keyword) {
            $orConditions->add($qb->expr()->like('p.titleBg', ':keyword' . $i));
            $orConditions->add($qb->expr()->like('p.titleEn', ':keyword' . $i));
            $orConditions->add($qb->expr()->like('p.descriptionBg', ':keyword' . $i));
            $orConditions->add($qb->expr()->like('p.descriptionEn', ':keyword' . $i));
            $qb->setParameter('keyword' . $i, '%' . $keyword . '%');
        }

        $qb->andWhere($orConditions)
           ->orderBy('p.updatedAt', 'DESC')
           ->setMaxResults(20);

        return $qb->getQuery()->getResult();
    }

    private function getLocationLinkTitle(Property $property, string $locale): string
    {
        $location = match($locale) {
            'bg' => $property->getLocationBg(),
            'de' => $property->getLocationDe(),
            'ru' => $property->getLocationRu(),
            default => $property->getLocationEn()
        } ?: $property->getLocationBg();

        return match($locale) {
            'bg' => "Други имоти в {$location}",
            'de' => "Weitere Immobilien in {$location}",
            'ru' => "Другие объекты в {$location}",
            default => "Other properties in {$location}"
        };
    }

    private function getTypeLinkTitle(Property $property, string $locale): string
    {
        $type = $property->getType();
        
        return match($locale) {
            'bg' => "Подобни {$type} имоти",
            'de' => "Ähnliche {$type} Immobilien", 
            'ru' => "Похожие {$type} объекты",
            default => "Similar {$type} properties"
        };
    }

    private function getPriceLinkTitle(Property $property, string $locale): string
    {
        return match($locale) {
            'bg' => "Имоти в подобна ценова категория",
            'de' => "Immobilien in ähnlicher Preisklasse",
            'ru' => "Объекты в похожей ценовой категории", 
            default => "Properties in similar price range"
        };
    }
}
