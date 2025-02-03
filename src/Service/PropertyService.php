<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\PropertyRepository;
use App\Service\FileUploadService;

class PropertyService
{
    private PropertyRepository $propertyRepository;
    private FileUploadService $fileUploadService;

    public function __construct(
        PropertyRepository $propertyRepository,
        FileUploadService $fileUploadService
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->fileUploadService = $fileUploadService;
    }

    public function getFeaturedProperties(int $limit = 6): array
    {
        return $this->propertyRepository->getFeaturedProperties($limit);
    }

    public function getLatestProperties(int $limit = 3): array
    {
        return $this->propertyRepository->getLatestProperties($limit);
    }

    public function getProperty(int $id): ?array
    {
        return $this->propertyRepository->find($id);
    }

    public function searchProperties(array $criteria): array
    {
        return $this->propertyRepository->searchProperties($criteria);
    }

    public function createProperty(array $data, array $files = []): int
    {
        // Handle file uploads
        if (!empty($files['images'])) {
            $data['images'] = $this->handleImageUploads($files['images']);
        }

        if (!empty($files['pdf_flyer'])) {
            $data['pdf_flyer'] = $this->handlePdfUpload($files['pdf_flyer']);
        }

        return $this->propertyRepository->create($data);
    }

    public function updateProperty(int $id, array $data, array $files = []): bool
    {
        // Handle file uploads
        if (!empty($files['images'])) {
            $data['images'] = $this->handleImageUploads($files['images']);
        }

        if (!empty($files['pdf_flyer'])) {
            $data['pdf_flyer'] = $this->handlePdfUpload($files['pdf_flyer']);
        }

        return $this->propertyRepository->update($id, $data);
    }

    public function deleteProperty(int $id): bool
    {
        $property = $this->propertyRepository->find($id);
        if (!$property) {
            return false;
        }

        // Delete associated files
        if (!empty($property['images'])) {
            foreach ($property['images'] as $image) {
                $this->fileUploadService->deleteFile($image['image_path']);
            }
        }

        if (!empty($property['pdf_flyer'])) {
            $this->fileUploadService->deleteFile($property['pdf_flyer']);
        }

        return $this->propertyRepository->delete($id);
    }

    private function handleImageUploads(array $images): array
    {
        $uploadedImages = [];
        foreach ($images as $image) {
            $path = $this->fileUploadService->uploadFile($image, 'properties');
            if ($path) {
                $uploadedImages[] = $path;
            }
        }
        return $uploadedImages;
    }

    private function handlePdfUpload(array $file): ?string
    {
        return $this->fileUploadService->uploadFile($file, 'flyers');
    }
} 