<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FileUploadService
{
    private $imageManager;
    private $slugger;
    private $uploadDir;
    private $imageWidth;
    private $imageHeight;

    public function __construct(
        SluggerInterface $slugger,
        string $uploadDir,
        int $imageWidth = 800,
        int $imageHeight = 600
    ) {
        $this->slugger = $slugger;
        $this->uploadDir = $uploadDir;
        $this->imageWidth = $imageWidth;
        $this->imageHeight = $imageHeight;
        $this->imageManager = new ImageManager(new Driver());
    }

    private function resizeImage(string $path): void
    {
        try {
            $image = $this->imageManager->read($path);
            
            // Оптимизираме размера
            $image->scale(width: $this->imageWidth, height: $this->imageHeight);
            
            // Запазваме с качество 80%
            $image->save(quality: 80);
        } catch (\Exception $e) {
            throw new \Exception('Грешка при оптимизиране на изображението: ' . $e->getMessage());
        }
    }

    public function upload(UploadedFile $file, string $subdirectory = ''): string
    {
        try {
            // Генерираме уникално име за файла
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

            // Определяме целевата директория
            $targetDirectory = rtrim($this->uploadDir, '/') . '/uploads';
            if ($subdirectory) {
                $targetDirectory .= '/' . trim($subdirectory, '/');
            }

            // Проверяваме дали директорията съществува и има правилните права
            if (!is_dir($targetDirectory)) {
                if (!mkdir($targetDirectory, 0777, true)) {
                    throw new \Exception('Грешка при създаване на директорията: ' . $targetDirectory);
                }
                $this->setFilePermissions($targetDirectory);
            }

            // Преместваме файла
            $file->move($targetDirectory, $fileName);

            // Задаваме правилните права на файла
            $this->setFilePermissions($targetDirectory . '/' . $fileName);

            return $fileName;
        } catch (\Exception $e) {
            throw new \Exception('Грешка при качване на файла: ' . $e->getMessage());
        }
    }

    public function getPublicPath(string $filename, string $subdirectory = ''): string
    {
        if ($subdirectory) {
            return '/uploads/' . trim($subdirectory, '/') . '/' . $filename;
        }
        return '/uploads/' . $filename;
    }

    public function deleteFile(string $filename, string $subdirectory = ''): void
    {
        $targetDirectory = $this->uploadDir;
        if ($subdirectory) {
            $targetDirectory .= '/' . trim($subdirectory, '/');
        }

        $filepath = $targetDirectory . '/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    public function setFilePermissions(string $path): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // За Windows използваме icacls
            exec(sprintf('icacls "%s" /grant Everyone:F', $path));
            if (dirname($path) !== $path) {
                exec(sprintf('icacls "%s" /grant Everyone:F', dirname($path)));
            }
        } else {
            // За Linux/Unix системи използваме chmod
            chmod($path, 0777);
            if (dirname($path) !== $path) {
                chmod(dirname($path), 0777);
            }
        }
    }

    public function removePropertyDirectory(string $propertyPath): void
    {
        $targetDirectory = $this->uploadDir . '/' . $propertyPath;
        if (is_dir($targetDirectory)) {
            $files = glob($targetDirectory . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($targetDirectory);
        }
    }

    public function remove(string $filename, string $subdirectory = ''): void
    {
        $filepath = rtrim($this->uploadDir, '/') . '/uploads';
        if ($subdirectory) {
            $filepath .= '/' . trim($subdirectory, '/');
        }
        $filepath .= '/' . $filename;

        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    public function uploadFile(UploadedFile $file, string $subdirectory = ''): string
    {
        return $this->upload($file, $subdirectory);
    }
}