<?php

namespace App\Controller;

use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private PropertyRepository $propertyRepository
    ) {}

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $vipProperties = $this->propertyRepository->findVipProperties();
        $featuredProperties = $this->propertyRepository->findFeaturedProperties();
        $latestProperties = $this->propertyRepository->findLatestProperties();

        return $this->render('home/index.html.twig', [
            'vip_properties' => $vipProperties,
            'featured_properties' => $featuredProperties,
            'latest_properties' => $latestProperties,
        ]);
    }
} 