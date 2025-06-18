<?php

namespace App\Controller\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServicesController extends AbstractController
{
    #[Route('/services', name: 'app_services')]
    public function index(): Response
    {        $services = [
            [
                'icon' => 'trending_up',
                'title' => 'services.market_analysis.title',
                'description' => 'services.market_analysis.description',
                'features' => [
                    'Детайлни пазарни отчети',
                    'Прогнози за цените',
                    'Анализ на конкуренцията'
                ]
            ],
            [
                'icon' => 'business',
                'title' => 'services.property_management.title',
                'description' => 'services.property_management.description',
                'features' => [
                    '24/7 технически support',
                    'Управление на наематели',
                    'Редовни инспекции'
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
                    'Правна експертиза',
                    'Изготвяне на договори',
                    'Съдействие при разрешителни'
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
                    'Инвестиционни стратегии',
                    'Финансиране на проекти',
                    'ROI анализи'
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