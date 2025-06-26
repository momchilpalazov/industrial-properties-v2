<?php

namespace App\Controller;

use App\Service\SitemapService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{
    public function __construct(
        private SitemapService $sitemapService
    ) {}

    #[Route('/sitemap.xml', name: 'sitemap_index', methods: ['GET'])]
    public function index(): Response
    {
        $xml = $this->sitemapService->generateMainSitemap();
        
        $response = new Response($xml);
        $response->headers->set('Content-Type', 'text/xml; charset=utf-8');
        $response->headers->set('Cache-Control', 'public, max-age=3600');
        
        return $response;
    }
    
    #[Route('/sitemap-{_locale}.xml', name: 'sitemap_language', requirements: ['_locale' => 'bg|en|de|ru'], methods: ['GET'])]
    public function language(string $_locale): Response
    {
        $xml = $this->sitemapService->generateLanguageSitemap($_locale);
        
        $response = new Response($xml);
        $response->headers->set('Content-Type', 'text/xml; charset=utf-8');
        $response->headers->set('Cache-Control', 'public, max-age=3600');
        
        return $response;
    }
    
    #[Route('/sitemap-main.xml', name: 'sitemap_main', methods: ['GET'])]
    public function main(): Response
    {
        return $this->language('en'); // Default to English for main sitemap
    }
    
    #[Route('/sitemap-images.xml', name: 'sitemap_images', methods: ['GET'])]
    public function images(): Response
    {
        $xml = $this->sitemapService->generateImagesSitemap();
        
        $response = new Response($xml);
        $response->headers->set('Content-Type', 'text/xml; charset=utf-8');
        $response->headers->set('Cache-Control', 'public, max-age=7200');
        
        return $response;
    }
}
