<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Service\PropertyService;
use App\Service\BlogService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\PropertyRepository;
use App\Repository\AboutSettingsRepository;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private PropertyService $propertyService;
    private BlogService $blogService;
    private PropertyRepository $propertyRepository;
    private AboutSettingsRepository $aboutSettingsRepository;

    public function __construct(
        PropertyService $propertyService,
        BlogService $blogService,
        PropertyRepository $propertyRepository,
        AboutSettingsRepository $aboutSettingsRepository
    ) {
        $this->propertyService = $propertyService;
        $this->blogService = $blogService;
        $this->propertyRepository = $propertyRepository;
        $this->aboutSettingsRepository = $aboutSettingsRepository;
    }

    #[Route('/', name: 'app_home', defaults: ['_locale' => 'bg'])]
    public function index(Request $request): Response
    {
        $currentLanguage = $request->getLocale();
        
        $vipProperties = $this->propertyRepository->findVipProperties(6);
        $featuredProperties = $this->propertyRepository->findFeatured(6);
        $latestProperties = $this->propertyRepository->findLatest(3);
        $latestPosts = $this->blogService->getLatestPosts(3, $currentLanguage);
        $propertyStats = $this->propertyRepository->getPropertyTypeStats();

        return $this->render('home/index.html.twig', [
            'vip_properties' => $vipProperties,
            'featured_properties' => $featuredProperties,
            'latest_properties' => $latestProperties,
            'latest_posts' => $latestPosts,
            'current_language' => $currentLanguage,
            'property_stats' => $propertyStats
        ]);
    }

    #[Route('/about', name: 'app_about', defaults: ['_locale' => 'bg'])]
    public function about(): Response
    {
        $settings = $this->aboutSettingsRepository->getSettings();
        
        return $this->render('home/about.html.twig', [
            'settings' => $settings
        ]);
    }

    #[Route('/contact', name: 'app_contact', defaults: ['_locale' => 'bg'])]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig');
    }
} 