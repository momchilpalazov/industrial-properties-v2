<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploadService
{
    public function __construct(
        private string $uploadDir,
        private SluggerInterface $slugger
    ) {}

    public function upload(UploadedFile $file, string $subdirectory = ''): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $targetDirectory = $this->getTargetDirectory($subdirectory);

        try {
            $file->move($targetDirectory, $fileName);
        } catch (FileException $e) {
            throw new \Exception('Възникна грешка при качването на файла: ' . $e->getMessage());
        }

        return $subdirectory ? $subdirectory . '/' . $fileName : $fileName;
    }

    public function remove(string $filename): void
    {
        $filepath = $this->uploadDir . '/' . $filename;
        
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    private function getTargetDirectory(string $subdirectory = ''): string
    {
        $targetDirectory = $this->uploadDir;
        
        if ($subdirectory) {
            $targetDirectory .= '/' . $subdirectory;
            
            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
            }
        }

        return $targetDirectory;
    }

    public function getPublicPath(string $filename): string
    {
        return '/uploads/' . $filename;
    }
} 