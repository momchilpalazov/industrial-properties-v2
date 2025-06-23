<?php

namespace App\Controller\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServicesController extends AbstractController
{    #[Route('/services', name: 'app_services')]
    public function index(): Response
    {        $services = [
            [
                'icon' => 'trending_up',
                'title' => 'services.market_analysis.title',
                'description' => 'services.market_analysis.description',
                'features' => [
                    'services.market_analysis.features.market_reports',
                    'services.market_analysis.features.price_forecasts',
                    'services.market_analysis.features.competition_analysis'
                ]
            ],
            [
                'icon' => 'business',
                'title' => 'services.property_management.title',
                'description' => 'services.property_management.description',
                'features' => [
                    'services.property_management.features.technical_support',
                    'services.property_management.features.tenant_management',
                    'services.property_management.features.regular_inspections'
                ]
            ],
            [
                'icon' => 'savings',
                'title' => 'services.cost_optimization.title',
                'description' => 'services.cost_optimization.description',
            ],
            [
                'icon' => 'gavel',
                'title' => 'services.legal.title',
                'description' => 'services.legal.description',
                'features' => [
                    'services.legal.features.legal_expertise',
                    'services.legal.features.contract_preparation',
                    'services.legal.features.permit_assistance'
                ]
            ],
            [
                'icon' => 'groups',
                'title' => 'services.hr.title',
                'description' => 'services.hr.description',
            ],
            [
                'icon' => 'public',
                'title' => 'services.international.title',
                'description' => 'services.international.description',
            ],
            [
                'icon' => 'account_balance',
                'title' => 'services.financial.title',
                'description' => 'services.financial.description',
                'features' => [
                    'services.financial.features.investment_strategies',
                    'services.financial.features.project_financing',
                    'services.financial.features.roi_analysis'
                ]
            ],
            [
                'icon' => 'support_agent',
                'title' => 'services.consulting.title',
                'description' => 'services.consulting.description',
            ]
        ];

        return $this->render('services/index.html.twig', [
            'services' => $services
        ]);
    }
} 