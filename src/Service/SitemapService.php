<?php

namespace App\Service;

use App\Repository\PropertyRepository;
use App\Repository\BlogPostRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapService
{
    public function __construct(
        private PropertyRepository $propertyRepository,
        private BlogPostRepository $blogPostRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function generateMainSitemap(): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>');
        
        // Main sitemap
        $sitemap = $xml->addChild('sitemap');
        $sitemap->addChild('loc', $this->urlGenerator->generate('sitemap_main', [], UrlGeneratorInterface::ABSOLUTE_URL));
        $sitemap->addChild('lastmod', date('Y-m-d\TH:i:s\Z'));
        
        // Language-specific sitemaps
        $languages = ['bg', 'en', 'de', 'ru'];
        foreach ($languages as $lang) {
            $sitemap = $xml->addChild('sitemap');
            $sitemap->addChild('loc', $this->urlGenerator->generate('sitemap_language', ['_locale' => $lang], UrlGeneratorInterface::ABSOLUTE_URL));
            $sitemap->addChild('lastmod', date('Y-m-d\TH:i:s\Z'));
        }
        
        // Images sitemap
        $sitemap = $xml->addChild('sitemap');
        $sitemap->addChild('loc', $this->urlGenerator->generate('sitemap_images', [], UrlGeneratorInterface::ABSOLUTE_URL));
        $sitemap->addChild('lastmod', date('Y-m-d\TH:i:s\Z'));
        
        return $xml->asXML();
    }
    
    public function generateLanguageSitemap(string $locale): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml"></urlset>');
        
        // Homepage
        $this->addUrlToSitemap($xml, 'app_home', ['_locale' => $locale], 1.0, 'daily');
        
        // Static pages
        $staticPages = [
            'app_property_index' => 0.9,
            'app_renting' => 0.8,
            'app_auctions_index' => 0.7,
            'app_services' => 0.6,
            'app_about' => 0.5,
            'app_contact' => 0.5,
            'app_faq' => 0.4,
            'app_blog_index' => 0.7
        ];
        
        foreach ($staticPages as $route => $priority) {
            $this->addUrlToSitemap($xml, $route, ['_locale' => $locale], $priority, 'weekly');
        }
        
        // Properties
        $properties = $this->propertyRepository->findAllPublished();
        foreach ($properties as $property) {
            $this->addUrlToSitemap(
                $xml, 
                'app_property_show', 
                ['_locale' => $locale, 'id' => $property->getId()], 
                0.8, 
                'weekly',
                $property->getUpdatedAt()
            );
        }
        
        // Blog posts
        $blogPosts = $this->blogPostRepository->findAllPublished();
        foreach ($blogPosts as $post) {
            $this->addUrlToSitemap(
                $xml, 
                'app_blog_show', 
                ['_locale' => $locale, 'slug' => $post->getSlug()], 
                0.6, 
                'monthly',
                $post->getUpdatedAt()
            );
        }
        
        return $xml->asXML();
    }
    
    public function generateImagesSitemap(): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"></urlset>');
        
        $properties = $this->propertyRepository->findAllPublished();
        
        foreach ($properties as $property) {        $url = $xml->addChild('url');
        $url->addChild('loc', $this->urlGenerator->generate('app_property_show', ['_locale' => 'en', 'id' => $property->getId()], UrlGeneratorInterface::ABSOLUTE_URL));
            
            foreach ($property->getImages() as $image) {
                $imageElement = $url->addChild('image:image', '', 'http://www.google.com/schemas/sitemap-image/1.1');
                $imageElement->addChild('image:loc', $this->urlGenerator->generate('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL) . 'uploads/properties/' . $image->getFilename(), 'http://www.google.com/schemas/sitemap-image/1.1');
                $imageElement->addChild('image:title', htmlspecialchars($property->getTitleEn()), 'http://www.google.com/schemas/sitemap-image/1.1');
                $imageElement->addChild('image:caption', htmlspecialchars($property->getLocationEn()), 'http://www.google.com/schemas/sitemap-image/1.1');
            }
        }
        
        return $xml->asXML();
    }
    
    private function addUrlToSitemap(\SimpleXMLElement $xml, string $route, array $params, float $priority, string $changefreq, ?\DateTimeInterface $lastmod = null): void
    {
        $url = $xml->addChild('url');
        $url->addChild('loc', $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL));
        $url->addChild('lastmod', $lastmod ? $lastmod->format('Y-m-d\TH:i:s\Z') : date('Y-m-d\TH:i:s\Z'));
        $url->addChild('changefreq', $changefreq);
        $url->addChild('priority', (string) $priority);
        
        // Add alternate language links for international SEO
        if (isset($params['_locale'])) {
            $languages = ['bg', 'en', 'de', 'ru'];
            foreach ($languages as $lang) {
                $altParams = array_merge($params, ['_locale' => $lang]);
                $link = $url->addChild('xhtml:link', '', 'http://www.w3.org/1999/xhtml');
                $link->addAttribute('rel', 'alternate');
                $link->addAttribute('hreflang', $lang);
                $link->addAttribute('href', $this->urlGenerator->generate($route, $altParams, UrlGeneratorInterface::ABSOLUTE_URL));
            }
        }
    }
}
