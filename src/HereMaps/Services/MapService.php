<?php

namespace App\HereMaps\Services;

use App\HereMaps\Config\HereMapsConfig;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class MapService
{
    private HereMapsConfig $config;
    private Environment $twig;
    private array $errors = [];
    private array $warnings = [];

    public function __construct(HereMapsConfig $config, Environment $twig)
    {
        $this->config = $config;
        $this->twig = $twig;
        
        // Проверка на API ключа при инициализация
        $this->validateApiKey();
    }

    public function renderMap(array $options = []): Response
    {
        $mapOptions = array_merge([
            'apiKey' => $this->config->getApiKey(),
            'center' => $this->config->getDefaultCenter(),
            'zoom' => $this->config->getDefaultZoom(),
            'markers' => []
        ], $options);
        
        // Добавяме дебъг информация от конфигурацията
        if (isset($mapOptions['debug']) && is_array($mapOptions['debug'])) {
            $mapOptions['debug'] = array_merge(
                $mapOptions['debug'], 
                ['config_debug' => $this->config->getDebugInfo()]
            );
        } else {
            $mapOptions['debug'] = [
                'config_debug' => $this->config->getDebugInfo()
            ];
        }
        
        // Добавяме грешки и предупреждения
        $mapOptions['debug']['errors'] = $this->errors;
        $mapOptions['debug']['warnings'] = $this->warnings;

        return new Response(
            $this->twig->render('here-maps/map.html.twig', [
                'options' => $mapOptions
            ])
        );
    }

    public function showSingleMarker(array $marker): Response
    {
        return $this->renderMap([
            'markers' => [$marker],
            'center' => [
                'lat' => $marker['lat'],
                'lng' => $marker['lng']
            ]
        ]);
    }

    public function showMultipleMarkers(array $markers): Response
    {
        return $this->renderMap([
            'markers' => $markers
        ]);
    }
    
    public function renderTestMap(array $options = []): Response
    {
        $mapOptions = array_merge([
            'apiKey' => $this->config->getApiKey(),
            'center' => $this->config->getDefaultCenter(),
            'zoom' => $this->config->getDefaultZoom(),
            'markers' => []
        ], $options);
        
        // Добавяме дебъг информация
        if (isset($mapOptions['debug']) && is_array($mapOptions['debug'])) {
            $mapOptions['debug'] = array_merge(
                $mapOptions['debug'], 
                ['config_debug' => $this->config->getDebugInfo()]
            );
        } else {
            $mapOptions['debug'] = [
                'config_debug' => $this->config->getDebugInfo()
            ];
        }
        
        // Добавяме грешки и предупреждения
        $mapOptions['debug']['errors'] = $this->errors;
        $mapOptions['debug']['warnings'] = $this->warnings;

        return new Response(
            $this->twig->render('here-maps/test.html.twig', [
                'options' => $mapOptions
            ])
        );
    }
    
    private function validateApiKey(): void
    {
        $apiKey = $this->config->getApiKey();
        
        if (empty($apiKey)) {
            $this->addError('API ключът е празен. Моля, проверете вашия .env или .env.local файл.');
        } elseif (strlen($apiKey) < 30) {
            $this->addWarning('API ключът изглежда твърде кратък. Here Maps API ключовете обикновено са около 40+ символа.');
        }
        
        // Проверяваме дали е използван тестов ключ
        if (str_contains($apiKey, 'TEST_KEY') || str_contains($apiKey, 'PLACEHOLDER')) {
            $this->addWarning('Използвате тестов ключ. Моля, задайте валиден Here Maps API ключ.');
        }
    }
    
    private function addError(string $message): void
    {
        $this->errors[] = $message;
    }
    
    private function addWarning(string $message): void
    {
        $this->warnings[] = $message;
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function getWarnings(): array
    {
        return $this->warnings;
    }
} 