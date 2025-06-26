<?php

namespace App\Service;

use App\Entity\Property;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PropertyStructuredDataService
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function generatePropertySchema(Property $property, string $locale = 'en'): array
    {
        $images = [];
        foreach ($property->getImages() as $image) {
            $images[] = $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL) . 'uploads/properties/' . $image->getFilename();
        }

        $schema = [
            "@context" => "https://schema.org",
            "@type" => "RealEstate",
            "name" => $this->getLocalizedTitle($property, $locale),
            "description" => $this->getLocalizedDescription($property, $locale),
            "url" => $this->urlGenerator->generate('app_property_show', [
                '_locale' => $locale, 
                'slug' => $property->getSlug()
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            "identifier" => $property->getId(),
            "category" => $this->mapPropertyTypeToSchema($property->getType()),
            "address" => [
                "@type" => "PostalAddress",
                "addressLocality" => $this->getLocalizedLocation($property, $locale),
                "addressCountry" => $this->getCountryFromLocation($property)
            ]
        ];

        // Add price if available
        if ($property->getPrice()) {
            $schema["offers"] = [
                "@type" => "Offer",
                "price" => $property->getPrice(),
                "priceCurrency" => "EUR",
                "availability" => $this->mapStatusToSchema($property->getStatus()),
                "seller" => [
                    "@type" => "Organization",
                    "name" => "Industrial Properties Europe"
                ]
            ];
        }

        // Add area if available
        if ($property->getArea()) {
            $schema["floorSize"] = [
                "@type" => "QuantitativeValue",
                "value" => $property->getArea(),
                "unitCode" => "MTK" // Square meters
            ];
        }

        // Add images
        if (!empty($images)) {
            $schema["image"] = $images;
        }

        // Add features if available
        if ($property->getFeatures() && count($property->getFeatures()) > 0) {
            $amenityFeatures = [];
            foreach ($property->getFeatures() as $feature) {
                $featureName = match($locale) {
                    'bg' => $feature->getFeatureBg(),
                    'de' => $feature->getFeatureDe() ?: $feature->getFeatureBg(),
                    'ru' => $feature->getFeatureRu() ?: $feature->getFeatureBg(),
                    default => $feature->getFeatureEn() ?: $feature->getFeatureBg()
                };
                
                if ($featureName) {
                    $amenityFeatures[] = [
                        "@type" => "LocationFeatureSpecification",
                        "name" => $featureName,
                        "value" => true
                    ];
                }
            }
            if (!empty($amenityFeatures)) {
                $schema["amenityFeature"] = $amenityFeatures;
            }
        }

        // Add geo coordinates if available
        if ($property->getLatitude() && $property->getLongitude()) {
            $schema["geo"] = [
                "@type" => "GeoCoordinates",
                "latitude" => $property->getLatitude(),
                "longitude" => $property->getLongitude()
            ];
        }

        return $schema;
    }

    public function generateBreadcrumbSchema(Property $property, string $locale = 'en'): array
    {
        return [
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => [
                [
                    "@type" => "ListItem",
                    "position" => 1,
                    "name" => "Home",
                    "item" => $this->urlGenerator->generate('app_home', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL)
                ],
                [
                    "@type" => "ListItem",
                    "position" => 2,
                    "name" => "Properties",
                    "item" => $this->urlGenerator->generate('app_property_index', ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL)
                ],
                [
                    "@type" => "ListItem",
                    "position" => 3,
                    "name" => $this->getLocalizedTitle($property, $locale),
                    "item" => $this->urlGenerator->generate('app_property_show', [
                        '_locale' => $locale, 
                        'slug' => $property->getSlug()
                    ], UrlGeneratorInterface::ABSOLUTE_URL)
                ]
            ]
        ];
    }

    public function generateOrganizationSchema(): array
    {
        return [
            "@context" => "https://schema.org",
            "@type" => "RealEstateAgent",
            "name" => "Industrial Properties Europe",
            "url" => $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
            "logo" => $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL) . 'images/logo.svg',
            "description" => "Premier industrial property platform for Europe, specializing in warehouses, manufacturing facilities, and logistics centers.",
            "areaServed" => [
                [
                    "@type" => "Country",
                    "name" => "Bulgaria"
                ],
                [
                    "@type" => "AdministrativeArea", 
                    "name" => "Europe"
                ]
            ],
            "serviceType" => [
                "Industrial Property Sales",
                "Warehouse Rental", 
                "Manufacturing Facility Leasing",
                "Logistics Center Sales",
                "Industrial Land Development"
            ],
            "knowsLanguage" => ["Bulgarian", "English", "German", "Russian"],
            "contactPoint" => [
                "@type" => "ContactPoint",
                "contactType" => "Customer Service",
                "availableLanguage" => ["Bulgarian", "English", "German", "Russian"],
                "areaServed" => "Europe"
            ]
        ];
    }

    private function getLocalizedTitle(Property $property, string $locale): string
    {
        return match($locale) {
            'bg' => $property->getTitleBg() ?: $property->getTitleEn(),
            'de' => $property->getTitleDe() ?: $property->getTitleEn(),
            'ru' => $property->getTitleRu() ?: $property->getTitleEn(),
            default => $property->getTitleEn() ?: $property->getTitleBg()
        };
    }

    private function getLocalizedDescription(Property $property, string $locale): string
    {
        $description = match($locale) {
            'bg' => $property->getDescriptionBg() ?: $property->getDescriptionEn(),
            'de' => $property->getDescriptionDe() ?: $property->getDescriptionEn(),
            'ru' => $property->getDescriptionRu() ?: $property->getDescriptionEn(),
            default => $property->getDescriptionEn() ?: $property->getDescriptionBg()
        };

        return strip_tags($description);
    }

    private function getLocalizedLocation(Property $property, string $locale): string
    {
        return match($locale) {
            'bg' => $property->getLocationBg() ?: $property->getLocationEn(),
            'de' => $property->getLocationDe() ?: $property->getLocationEn(),
            'ru' => $property->getLocationRu() ?: $property->getLocationEn(),
            default => $property->getLocationEn() ?: $property->getLocationBg()
        };
    }

    private function getCountryFromLocation(Property $property): string
    {
        // Simple logic to determine country from location
        $location = strtolower($property->getLocationBg() ?: $property->getLocationEn());
        
        if (str_contains($location, 'софия') || str_contains($location, 'софия') || 
            str_contains($location, 'пловдив') || str_contains($location, 'варна') || 
            str_contains($location, 'бургас') || str_contains($location, 'bulgaria')) {
            return "BG";
        }
        
        if (str_contains($location, 'germany') || str_contains($location, 'deutschland')) {
            return "DE";
        }
        
        if (str_contains($location, 'russia') || str_contains($location, 'россия')) {
            return "RU";
        }
        
        return "BG"; // Default to Bulgaria
    }

    private function mapPropertyTypeToSchema(?string $type): string
    {
        return match($type) {
            'warehouse' => 'Warehouse',
            'logistics_center' => 'Warehouse', 
            'production_facility' => 'IndustrialBuilding',
            'industrial_land' => 'LandParcel',
            'office' => 'OfficeBuilding',
            default => 'CommercialRealEstate'
        };
    }

    private function mapStatusToSchema(?string $status): string
    {
        return match($status) {
            Property::STATUS_AVAILABLE => "https://schema.org/InStock",
            Property::STATUS_RESERVED => "https://schema.org/PreOrder",
            Property::STATUS_SOLD => "https://schema.org/OutOfStock",
            Property::STATUS_RENTED => "https://schema.org/OutOfStock",
            Property::STATUS_PENDING => "https://schema.org/PreOrder",
            Property::STATUS_AUCTION => "https://schema.org/InStock",
            default => "https://schema.org/InStock"
        };
    }
}
