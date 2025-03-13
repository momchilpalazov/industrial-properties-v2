<?php

namespace App\HereMaps\Config;

class HereMapsConfig
{
    private string $apiKey;
    private float $defaultLat;
    private float $defaultLng;
    private int $defaultZoom;
    private array $debugInfo = [];

    public function __construct(array $config = [])
    {
        // Проверяваме дали API ключът е зареден
        $envApiKey = $_ENV['HERE_MAPS_API_KEY'] ?? '';
        
        // Приоритет: 1. Подадена конфигурация, 2. .env променливи
        $this->apiKey = $config['api_key'] ?? $envApiKey;
        $this->defaultLat = $config['default_lat'] ?? (float)($_ENV['HERE_MAPS_DEFAULT_LAT'] ?? 42.6977);
        $this->defaultLng = $config['default_lng'] ?? (float)($_ENV['HERE_MAPS_DEFAULT_LNG'] ?? 23.3219);
        $this->defaultZoom = $config['default_zoom'] ?? (int)($_ENV['HERE_MAPS_DEFAULT_ZOOM'] ?? 13);
        
        // Съхраняваме дебъг информация
        $this->debugInfo = [
            'source' => empty($config['api_key']) ? 'env' : 'config',
            'env_api_key_exists' => !empty($envApiKey),
            'env_api_key_length' => strlen($envApiKey),
            'config_api_key_exists' => isset($config['api_key']),
            'final_api_key_length' => strlen($this->apiKey),
        ];
        
        // Нормализираме API ключа (премахваме празни пространства)
        $this->apiKey = trim($this->apiKey);
        
        // Ако API ключът е празен, използваме тестов ключ от .env.local
        if (empty($this->apiKey)) {
            // Тук можем да генерираме грешка или да използваме тестов ключ
            $this->apiKey = 'TEST_KEY_PLACEHOLDER';
            $this->debugInfo['warning'] = 'Използван е тестов ключ, тъй като не е намерен валиден API ключ';
        }
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getDefaultLat(): float
    {
        return $this->defaultLat;
    }

    public function getDefaultLng(): float
    {
        return $this->defaultLng;
    }

    public function getDefaultZoom(): int
    {
        return $this->defaultZoom;
    }

    public function getDefaultCenter(): array
    {
        return [
            'lat' => $this->defaultLat,
            'lng' => $this->defaultLng
        ];
    }
    
    public function getDebugInfo(): array
    {
        return $this->debugInfo;
    }
    
    public function isApiKeyValid(): bool
    {
        // Проста валидация на дължината (Here Maps API ключовете обикновено са около 40+ символа)
        return strlen($this->apiKey) >= 30;
    }
    
    public function getApiKeySource(): string
    {
        return $this->debugInfo['source'];
    }
} 