<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FileUploadService
{
    private $uploadDir;
    private $imageWidth;
    private $imageHeight;
    private $slugger;
    private $imageManager;

    public function __construct(
        string $uploadDir,
        int $imageWidth,
        int $imageHeight,
        SluggerInterface $slugger
    ) {
        $this->uploadDir = $uploadDir;
        $this->imageWidth = $imageWidth;
        $this->imageHeight = $imageHeight;
        $this->slugger = $slugger;
        $this->imageManager = new ImageManager(new Driver());
    }

    private function resizeImage(string $path): void
    {
        $image = $this->imageManager->read($path);
        
        // Запазваме съотношението на страните
        $image->cover($this->imageWidth, $this->imageHeight);
        
        // Запазваме качеството на 80%
        $image->save($path, quality: 80);
    }

    public function upload(UploadedFile $file, string $subdirectory = ''): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $targetDirectory = $this->uploadDir;
        if ($subdirectory) {
            $targetDirectory .= '/' . trim($subdirectory, '/');
        }

        try {
            $file->move($targetDirectory, $fileName);

            // Оптимизиране на изображението
            if (in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
                $this->resizeImage($targetDirectory . '/' . $fileName);
            }
        } catch (FileException $e) {
            throw new FileException('Възникна грешка при качването на файла');
        }

        return $fileName;
    }

    public function remove(string $filename, string $subdirectory = ''): bool
    {
        $targetDirectory = $this->uploadDir;
        if ($subdirectory) {
            $targetDirectory .= '/' . trim($subdirectory, '/');
        }

        $filepath = $targetDirectory . '/' . $filename;

        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return false;
    }

    private function getTargetDirectory(string $subdirectory = ''): string
    {
        $targetDirectory = rtrim($this->uploadDir, '/');
        $this->setFilePermissions($targetDirectory);
        
        if ($subdirectory) {
            $parts = explode('/', trim($subdirectory, '/'));
            $currentPath = $targetDirectory;
            
            foreach ($parts as $part) {
                $currentPath .= '/' . $part;
                if (!is_dir($currentPath)) {
                    mkdir($currentPath, 0777, true);
                }
                $this->setFilePermissions($currentPath);
            }
            
            return $currentPath;
        }

        return $targetDirectory;
    }

    public function getPublicPath(string $filename, ?int $propertyId = null): string
    {
        if ($propertyId) {
            return '/img/properties/' . $propertyId . '/' . $filename;
        }
        return '/img/properties/' . $filename;
    }

    public function setFilePermissions(string $path): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            try {
                // За Windows използваме icacls
                $command = sprintf(
                    'icacls "%s" /grant "IUSR:(OI)(CI)F" /grant "IIS_IUSRS:(OI)(CI)F" /grant "NETWORK SERVICE:(OI)(CI)F" /T /C',
                    str_replace('/', '\\', $path)
                );
                exec($command);
                
                // Добавяме права и за родителската директория
                if (dirname($path) !== $path) {
                    $parentPath = dirname($path);
                    $parentCommand = sprintf(
                        'icacls "%s" /grant "IUSR:(OI)(CI)F" /grant "IIS_IUSRS:(OI)(CI)F" /grant "NETWORK SERVICE:(OI)(CI)F" /T /C',
                        str_replace('/', '\\', $parentPath)
                    );
                    exec($parentCommand);
                }
            } catch (\Exception $e) {
                throw new \Exception('Грешка при задаване на права: ' . $e->getMessage());
            }
        } else {
            // За Linux/Unix системи използваме chmod
            chmod($path, 0777);
            // Добавяме права и за родителската директория
            if (dirname($path) !== $path) {
                chmod(dirname($path), 0777);
            }
        }
    }

    public function removePropertyDirectory(int $propertyId): void
    {
        $directory = $this->uploadDir . '/properties/' . $propertyId;
        
        if (is_dir($directory)) {
            $files = scandir($directory);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    unlink($directory . '/' . $file);
                }
            }
            rmdir($directory);
        }
    }
} 