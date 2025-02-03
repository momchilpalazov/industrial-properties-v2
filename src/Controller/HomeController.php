<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PropertyService;
use App\Service\BlogService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class HomeController extends AbstractController
{
    private PropertyService $propertyService;
    private BlogService $blogService;

    public function __construct(
        Environment $twig,
        PropertyService $propertyService,
        BlogService $blogService
    ) {
        parent::__construct($twig);
        $this->propertyService = $propertyService;
        $this->blogService = $blogService;
    }

    public function __invoke(Request $request): Response
    {
        $currentLanguage = $request->getLocale();
        
        $featuredProperties = $this->propertyService->getFeaturedProperties(6);
        $latestProperties = $this->propertyService->getLatestProperties(3);
        $latestPosts = $this->blogService->getLatestPosts(3, $currentLanguage);

        return $this->render('home/index.html.twig', [
            'featured_properties' => $featuredProperties,
            'latest_properties' => $latestProperties,
            'latest_posts' => $latestPosts,
            'current_language' => $currentLanguage
        ]);
    }
} 