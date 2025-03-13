<?php

namespace App\Controller;

use App\HereMaps\Config\HereMapsConfig;
use App\HereMaps\Services\MapService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/test/here-maps', name: 'app_here_maps_')]
class HereMapsTestController extends AbstractController
{
    private MapService $mapService;
    private HereMapsConfig $config;

    public function __construct(MapService $mapService, HereMapsConfig $config)
    {
        $this->mapService = $mapService;
        $this->config = $config;
    }

    #[Route('', name: 'test')]
    public function index(): Response
    {
        // Събираме дебъг информация
        $debug = [
            'api_key' => $this->config->getApiKey(),
            'api_key_length' => strlen($this->config->getApiKey()),
            'server_info' => [
                'php_version' => PHP_VERSION,
                'os' => PHP_OS,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            ],
            'env_vars' => array_filter($_ENV, function($key) {
                return strpos($key, 'HERE_') === 0;
            }, ARRAY_FILTER_USE_KEY),
            'env_file_loaded' => file_exists('.env') ? 'Yes' : 'No',
            'env_local_file_loaded' => file_exists('.env.local') ? 'Yes' : 'No',
            'symfony_env' => $_ENV['APP_ENV'] ?? 'Unknown'
        ];

        // Опции за картата
        $options = [
            'apiKey' => $this->config->getApiKey(),
            'center' => $this->config->getDefaultCenter(),
            'zoom' => $this->config->getDefaultZoom(),
            'markers' => [],
            'debug' => $debug
        ];

        return $this->render('here-maps/test.html.twig', [
            'options' => $options
        ]);
    }

    #[Route('/basic', name: 'basic')]
    public function basic(): Response
    {
        return $this->mapService->renderMap([
            'debug' => [
                'api_key' => $this->config->getApiKey(),
                'api_key_length' => strlen($this->config->getApiKey())
            ]
        ]);
    }

    #[Route('/marker', name: 'marker')]
    public function marker(): Response
    {
        // София
        $marker = [
            'lat' => 42.6977,
            'lng' => 23.3219,
            'title' => 'София'
        ];

        return $this->mapService->showSingleMarker($marker);
    }

    #[Route('/markers', name: 'markers')]
    public function markers(): Response
    {
        // Няколко града в България
        $markers = [
            [
                'lat' => 42.6977,
                'lng' => 23.3219,
                'title' => 'София'
            ],
            [
                'lat' => 42.1354,
                'lng' => 24.7453,
                'title' => 'Пловдив'
            ],
            [
                'lat' => 43.2141,
                'lng' => 27.9147,
                'title' => 'Варна'
            ],
            [
                'lat' => 42.4230,
                'lng' => 25.6250,
                'title' => 'Стара Загора'
            ]
        ];

        return $this->mapService->showMultipleMarkers($markers);
    }

    #[Route('/different-key', name: 'different_key')]
    public function differentKey(): Response
    {
        // Използвайте този метод, за да тествате различен API ключ
        $testApiKey = 'your_test_api_key_here'; // Заменете с ключ за тестване
        
        $options = [
            'apiKey' => $testApiKey,
            'center' => $this->config->getDefaultCenter(),
            'zoom' => $this->config->getDefaultZoom(),
            'markers' => [],
            'debug' => [
                'api_key' => $testApiKey,
                'api_key_length' => strlen($testApiKey),
                'original_key' => $this->config->getApiKey()
            ]
        ];

        return $this->render('here-maps/test.html.twig', [
            'options' => $options
        ]);
    }
} 