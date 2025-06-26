<?php

namespace App\Service;

use App\Entity\Property;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OgImageService
{
    private const DEFAULT_OG_IMAGE = 'images/og-default.jpg';
    private const PROPERTY_OG_WIDTH = 1200;
    private const PROPERTY_OG_HEIGHT = 630;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private string $projectDir
    ) {}

    public function getPropertyOgImage(Property $property): string
    {
        // If property has images, use the first one
        if ($property->getImages() && count($property->getImages()) > 0) {
            $firstImage = $property->getImages()->first();
            if ($firstImage) {
                return $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL) . 
                       'uploads/properties/' . $firstImage->getFilename();
            }
        }

        // Fallback to default OG image
        return $this->getDefaultOgImage();
    }

    public function getDefaultOgImage(): string
    {
        return $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL) . 
               self::DEFAULT_OG_IMAGE;
    }

    public function generateDynamicOgImage(Property $property, string $locale = 'en'): string
    {
        // This method can be implemented later for dynamic OG image generation
        // using libraries like Intervention Image or similar
        
        $title = $this->getLocalizedTitle($property, $locale);
        $location = $this->getLocalizedLocation($property, $locale);
        $price = $property->getPrice() ? number_format($property->getPrice(), 0, ',', ' ') . ' €' : '';
        
        // For now, return the property image or default
        return $this->getPropertyOgImage($property);
    }

    public function getBlogPostOgImage($blogPost): string
    {
        // If blog post has featured image, use it
        if (method_exists($blogPost, 'getFeaturedImage') && $blogPost->getFeaturedImage()) {
            return $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL) . 
                   'uploads/blog/' . $blogPost->getFeaturedImage();
        }

        // Fallback to default
        return $this->getDefaultOgImage();
    }

    public function getPageOgImage(string $pageName): string
    {
        $pageImages = [
            'home' => 'images/og-home.jpg',
            'properties' => 'images/og-properties.jpg',
            'about' => 'images/og-about.jpg',
            'contact' => 'images/og-contact.jpg',
            'services' => 'images/og-services.jpg',
            'blog' => 'images/og-blog.jpg'
        ];

        $imagePath = $pageImages[$pageName] ?? self::DEFAULT_OG_IMAGE;
        
        return $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL) . $imagePath;
    }

    public function generatePropertyImageAlt(Property $property, string $locale = 'en'): string
    {
        $title = $this->getLocalizedTitle($property, $locale);
        $location = $this->getLocalizedLocation($property, $locale);
        
        return sprintf('%s - %s', $title, $location);
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

    private function getLocalizedLocation(Property $property, string $locale): string
    {
        return match($locale) {
            'bg' => $property->getLocationBg() ?: $property->getLocationEn(),
            'de' => $property->getLocationDe() ?: $property->getLocationEn(),
            'ru' => $property->getLocationRu() ?: $property->getLocationEn(),
            default => $property->getLocationEn() ?: $property->getLocationBg()
        };
    }
}
