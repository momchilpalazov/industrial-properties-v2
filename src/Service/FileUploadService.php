<?php

declare(strict_types=1);

namespace App\Service;

use RuntimeException;

class FileUploadService
{
    private string $uploadDir;
    private int $maxFileSize;

    public function __construct(string $uploadDir, int $maxFileSize)
    {
        $this->uploadDir = rtrim($uploadDir, '/');
        $this->maxFileSize = $maxFileSize;
    }

    public function uploadFile(array $file, string $subDirectory = ''): ?string
    {
        $this->validateFile($file);

        $targetDir = $this->uploadDir . '/' . trim($subDirectory, '/');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $fileName = $this->generateUniqueFileName($file['name']);
        $targetPath = $targetDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        return $subDirectory ? $subDirectory . '/' . $fileName : $fileName;
    }

    public function deleteFile(string $path): bool
    {
        $fullPath = $this->uploadDir . '/' . ltrim($path, '/');
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    private function validateFile(array $file): void
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException('Invalid parameters.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('File size exceeds limit.');
            case UPLOAD_ERR_PARTIAL:
                throw new RuntimeException('File was only partially uploaded.');
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file was uploaded.');
            case UPLOAD_ERR_NO_TMP_DIR:
                throw new RuntimeException('Missing temporary folder.');
            case UPLOAD_ERR_CANT_WRITE:
                throw new RuntimeException('Failed to write file to disk.');
            case UPLOAD_ERR_EXTENSION:
                throw new RuntimeException('File upload stopped by extension.');
            default:
                throw new RuntimeException('Unknown upload error.');
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new RuntimeException('File size exceeds limit.');
        }

        // Validate file type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf'
        ];

        if (!in_array($mimeType, $allowedTypes)) {
            throw new RuntimeException('Invalid file type.');
        }
    }

    private function generateUniqueFileName(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return sprintf(
            '%s_%s.%s',
            pathinfo($originalName, PATHINFO_FILENAME),
            uniqid(),
            $extension
        );
    }
} 