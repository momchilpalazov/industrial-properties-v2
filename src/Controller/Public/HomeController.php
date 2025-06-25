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
            'settings' => [
                'title_bg' => $settings->getTitleBg(),
                'title_en' => $settings->getTitleEn(),
                'title_de' => $settings->getTitleDe(),
                'title_ru' => $settings->getTitleRu(),
                'subtitle_bg' => $settings->getSubtitleBg(),
                'subtitle_en' => $settings->getSubtitleEn(),
                'subtitle_de' => $settings->getSubtitleDe(),
                'subtitle_ru' => $settings->getSubtitleRu(),
                'content_bg' => $settings->getContentBg(),
                'content_en' => $settings->getContentEn(),
                'content_de' => $settings->getContentDe(),
                'content_ru' => $settings->getContentRu(),
                'company_image' => $settings->getCompanyImage(),
                'values_bg' => $settings->getValuesBg(),
                'values_en' => $settings->getValuesEn(),
                'values_de' => $settings->getValuesDe(),
                'values_ru' => $settings->getValuesRu(),
                'team' => $settings->getTeam(), // За обратна съвместимост
                'team_bg' => $settings->getTeamBg(),
                'team_en' => $settings->getTeamEn(),
                'team_de' => $settings->getTeamDe(),
                'team_ru' => $settings->getTeamRu(),
                'team_common' => $settings->getTeamCommon(),
                'meta_title_bg' => $settings->getMetaTitleBg(),
                'meta_title_en' => $settings->getMetaTitleEn(),
                'meta_title_de' => $settings->getMetaTitleDe(),
                'meta_title_ru' => $settings->getMetaTitleRu(),
                'meta_description_bg' => $settings->getMetaDescriptionBg(),
                'meta_description_en' => $settings->getMetaDescriptionEn(),
                'meta_description_de' => $settings->getMetaDescriptionDe(),
                'meta_description_ru' => $settings->getMetaDescriptionRu()
            ]
        ]);
    }

    #[Route('/contact', name: 'app_contact', defaults: ['_locale' => 'bg'])]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig');
    }
} 