<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Intervention\Image\ImageManager;
use Symfony\Component\Filesystem\Filesystem;

class ImageCacheService
{
    private $params;
    private $imageManager;
    private $filesystem;
    private $cacheDir;

    public function __construct(
        ParameterBagInterface $params,
        Filesystem $filesystem
    ) {
        $this->params = $params;
        $this->filesystem = $filesystem;
        $this->imageManager = new ImageManager(['driver' => 'gd']);
        $this->cacheDir = $this->params->get('kernel.project_dir') . '/public/cache/images';
        
        if (!$this->filesystem->exists($this->cacheDir)) {
            $this->filesystem->mkdir($this->cacheDir, 0777);
        }
    }

    public function getCachedImage(string $path, int $width = null, int $height = null): string
    {
        // Проверяваме дали пътят е валиден
        if (!$this->filesystem->exists($this->params->get('kernel.project_dir') . '/public/' . $path)) {
            return $path;
        }

        // Създаваме уникално име за кешираното изображение
        $cacheFileName = $this->generateCacheFileName($path, $width, $height);
        $cachedPath = $this->cacheDir . '/' . $cacheFileName;

        // Ако кешираното изображение вече съществува, връщаме неговия път
        if ($this->filesystem->exists($cachedPath)) {
            return '/cache/images/' . $cacheFileName;
        }

        // Зареждаме и обработваме изображението
        $image = $this->imageManager->make($this->params->get('kernel.project_dir') . '/public/' . $path);

        if ($width && $height) {
            $image->fit($width, $height);
        } elseif ($width) {
            $image->widen($width, function ($constraint) {
                $constraint->upsize();
            });
        } elseif ($height) {
            $image->heighten($height, function ($constraint) {
                $constraint->upsize();
            });
        }

        // Запазваме кешираното изображение
        $image->save($cachedPath, 80);

        return '/cache/images/' . $cacheFileName;
    }

    private function generateCacheFileName(string $path, ?int $width, ?int $height): string
    {
        $pathInfo = pathinfo($path);
        $hash = md5($path . $width . $height);
        
        return sprintf(
            '%s_%s_%s.%s',
            $pathInfo['filename'],
            $hash,
            ($width && $height) ? "{$width}x{$height}" : ($width ? "w{$width}" : "h{$height}"),
            $pathInfo['extension']
        );
    }
} 