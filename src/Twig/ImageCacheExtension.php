<?php

namespace App\Twig;

use App\Service\ImageCacheService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageCacheExtension extends AbstractExtension
{
    private $imageCacheService;

    public function __construct(ImageCacheService $imageCacheService)
    {
        $this->imageCacheService = $imageCacheService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('image_cache', [$this, 'getCachedImage']),
        ];
    }

    public function getCachedImage(string $path, int $width = null, int $height = null): string
    {
        return $this->imageCacheService->getCachedImage($path, $width, $height);
    }
} 