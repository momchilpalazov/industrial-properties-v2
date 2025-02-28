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
        if (!$file->isValid()) {
            throw new \Exception('Невалиден файл');
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $targetDirectory = $this->uploadDir;
        if ($subdirectory) {
            $targetDirectory .= '/' . trim($subdirectory, '/');
        }

        try {
            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
            }

            // Проверяваме дали имаме права за запис
            if (!is_writable($targetDirectory)) {
                throw new \Exception('Няма права за запис в директорията');
            }

            // Преместваме файла
            $file->move($targetDirectory, $fileName);

            // Проверяваме дали файлът е качен успешно
            if (!file_exists($targetDirectory . '/' . $fileName)) {
                throw new \Exception('Файлът не беше качен успешно');
            }

            // Оптимизираме изображението ако е снимка
            if (in_array($file->guessExtension(), ['jpg', 'jpeg', 'png', 'gif'])) {
                $this->resizeImage($targetDirectory . '/' . $fileName);
            }

            // Връщаме публичния път
            return '/uploads/images/' . $fileName;
        } catch (\Exception $e) {
            throw new \Exception('Грешка при качване на файла: ' . $e->getMessage());
        }
    }

    public function getPublicPath(string $filename, ?string $subdirectory = null): string
    {
        return '/uploads/images/' . $filename;
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

    private function setFilePermissions(string $path): void
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
}