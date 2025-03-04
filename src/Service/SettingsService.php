<?php

namespace App\Service;

use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class SettingsService
{
    private EntityManagerInterface $entityManager;
    private FilesystemAdapter $cache;
    private array $settings = [];
    private bool $loaded = false;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->cache = new FilesystemAdapter('settings', 3600, __DIR__ . '/../../var/cache');
    }

    public function get(string $key, $default = null)
    {
        $this->loadSettings();
        
        return $this->settings[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->loadSettings();
        
        $repository = $this->entityManager->getRepository(Setting::class);
        $setting = $repository->findOneBy(['key' => $key]);
        
        if (!$setting) {
            $setting = new Setting();
            $setting->setKey($key);
        }
        
        $setting->setValue($value);
        
        $this->entityManager->persist($setting);
        $this->entityManager->flush();
        
        $this->settings[$key] = $value;
        $this->cache->delete('all_settings');
    }

    public function getAll(): array
    {
        $this->loadSettings();
        
        return $this->settings;
    }

    private function loadSettings(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->settings = $this->cache->get('all_settings', function (ItemInterface $item) {
            $settings = [];
            $repository = $this->entityManager->getRepository(Setting::class);
            $allSettings = $repository->findAll();
            
            foreach ($allSettings as $setting) {
                $settings[$setting->getKey()] = $setting->getValue();
            }
            
            return $settings;
        });

        $this->loaded = true;
    }
} 