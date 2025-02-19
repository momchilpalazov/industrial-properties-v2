<?php

namespace App\Controller\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServicesController extends AbstractController
{
    #[Route('/services', name: 'app_services')]
    public function index(): Response
    {
        $services = [
            [
                'icon' => 'bi-graph-up',
                'title' => 'services.market_analysis.title',
                'description' => 'services.market_analysis.description',
            ],
            [
                'icon' => 'bi-building-gear',
                'title' => 'services.property_management.title',
                'description' => 'services.property_management.description',
            ],
            [
                'icon' => 'bi-cash-coin',
                'title' => 'services.cost_optimization.title',
                'description' => 'services.cost_optimization.description',
            ],
            [
                'icon' => 'bi-file-text',
                'title' => 'services.legal.title',
                'description' => 'services.legal.description',
            ],
            [
                'icon' => 'bi-people',
                'title' => 'services.hr.title',
                'description' => 'services.hr.description',
            ],
            [
                'icon' => 'bi-translate',
                'title' => 'services.international.title',
                'description' => 'services.international.description',
            ],
            [
                'icon' => 'bi-bank',
                'title' => 'services.financial.title',
                'description' => 'services.financial.description',
            ],
            [
                'icon' => 'bi-shield-check',
                'title' => 'services.consulting.title',
                'description' => 'services.consulting.description',
            ]
        ];

        return $this->render('services/index.html.twig', [
            'services' => $services
        ]);
    }
} 