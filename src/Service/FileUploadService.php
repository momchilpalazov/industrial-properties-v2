<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FileUploadService
{
    private ImageManager $imageManager;

    public function __construct(
        private string $uploadDir,
        private SluggerInterface $slugger,
        private int $imageWidth = 800,
        private int $imageHeight = 600
    ) {
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

    public function upload(UploadedFile $file, string $subdirectory = '', ?int $propertyId = null): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        // Конструираме пътя спрямо propertyId
        $relativePath = 'properties';
        if ($propertyId) {
            $relativePath .= '/' . $propertyId;
        }

        $targetDirectory = $this->getTargetDirectory($relativePath);
        $targetPath = $targetDirectory . '/' . $fileName;

        try {
            // Преоразмеряваме изображението преди да го преместим
            if (in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                // Създаваме временно копие на файла
                $tempPath = $file->getPathname();
                $image = $this->imageManager->read($tempPath);
                $image->cover($this->imageWidth, $this->imageHeight);
                
                // Запазваме преоразмереното изображение директно в целевата директория
                $image->save($targetPath, quality: 80);
            } else {
                // Ако не е изображение, просто го преместваме
                $file->move($targetDirectory, $fileName);
            }
            
            // След качване на файла, задаваме правилните права
            $this->setFilePermissions($targetPath);
            
        } catch (FileException $e) {
            throw new \Exception('Възникна грешка при качването на файла: ' . $e->getMessage());
        }

        return $propertyId ? $propertyId . '/' . $fileName : $fileName;
    }

    public function remove(string $filename, ?int $propertyId = null): void
    {
        // Вземаме само името на файла, без път
        $filename = basename($filename);
        
        // Конструираме пълния път до файла
        $filepath = $this->uploadDir . '/properties/';
        if ($propertyId) {
            $filepath .= $propertyId . '/';
        }
        $filepath .= $filename;
        
        if (file_exists($filepath)) {
            try {
                unlink($filepath);
                
                // Ако директорията е празна и това е директория на имот, изтриваме я
                if ($propertyId) {
                    $dir = dirname($filepath);
                    if (is_dir($dir) && count(scandir($dir)) <= 2) { // . и ..
                        rmdir($dir);
                    }
                }
            } catch (\Exception $e) {
                throw new \Exception('Грешка при изтриване на файла: ' . $e->getMessage());
            }
        }
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