<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\PropertyRepository;
use App\Service\FileUploadService;
use App\Entity\Property;
use App\Entity\PropertyImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class PropertyService
{
    private PropertyRepository $propertyRepository;
    private FileUploadService $fileUploadService;
    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;

    public function __construct(
        PropertyRepository $propertyRepository,
        FileUploadService $fileUploadService,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->fileUploadService = $fileUploadService;
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
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

    public function create(Property $property): void
    {
        // Генериране на slug
        $property->setSlug($this->generateSlug($property));
        
        // Задаване на timestamp
        $now = new \DateTimeImmutable();
        $property->setCreatedAt($now);
        $property->setUpdatedAt($now);

        $this->entityManager->persist($property);
        $this->entityManager->flush();
    }

    public function update(Property $property): void
    {
        // Обновяване на slug ако е променено заглавието
        $property->setSlug($this->generateSlug($property));
        
        // Обновяване на timestamp
        $property->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();
    }

    public function delete(Property $property): void
    {
        // Изтриване на всички снимки
        foreach ($property->getImages() as $image) {
            $this->fileUploadService->remove($image->getPath());
            $this->entityManager->remove($image);
        }

        $this->entityManager->remove($property);
        $this->entityManager->flush();
    }

    public function handleImages(Property $property, array $uploadedFiles, ?int $mainImageId = null): void
    {
        // Качване на нови снимки
        foreach ($uploadedFiles as $file) {
            if ($file instanceof UploadedFile) {
                $filename = $this->fileUploadService->upload($file, 'properties');
                
                $image = new PropertyImage();
                $image->setProperty($property);
                $image->setPath($filename);
                $image->setIsMain(false);
                
                $this->entityManager->persist($image);
            }
        }

        // Задаване на основна снимка
        if ($mainImageId) {
            $this->setMainImage($property, $mainImageId);
        }
        // Ако няма зададена основна снимка и това е първата снимка, я правим основна
        elseif (count($property->getImages()) === 1) {
            $firstImage = $property->getImages()->first();
            if ($firstImage) {
                $firstImage->setIsMain(true);
            }
        }

        $this->entityManager->flush();
    }

    public function deleteImage(Property $property, int $imageId): void
    {
        $image = $this->entityManager->getRepository(PropertyImage::class)->find($imageId);
        
        if ($image && $image->getProperty() === $property) {
            // Ако изтриваме основната снимка, правим първата следваща основна
            if ($image->isMain() && count($property->getImages()) > 1) {
                $nextImage = $property->getImages()
                    ->filter(fn(PropertyImage $img) => $img !== $image)
                    ->first();
                if ($nextImage) {
                    $nextImage->setIsMain(true);
                }
            }

            $this->fileUploadService->remove($image->getPath());
            $this->entityManager->remove($image);
            $this->entityManager->flush();
        }
    }

    public function reorderImages(Property $property, array $positions): void
    {
        foreach ($positions as $imageId => $position) {
            $image = $this->entityManager->getRepository(PropertyImage::class)->find($imageId);
            if ($image && $image->getProperty() === $property) {
                $image->setPosition($position);
            }
        }

        $this->entityManager->flush();
    }

    public function setMainImage(Property $property, int $imageId): void
    {
        // Премахване на основна снимка от всички снимки
        foreach ($property->getImages() as $image) {
            $image->setIsMain(false);
        }

        // Задаване на новата основна снимка
        $mainImage = $this->entityManager->getRepository(PropertyImage::class)->find($imageId);
        if ($mainImage && $mainImage->getProperty() === $property) {
            $mainImage->setIsMain(true);
            $this->entityManager->flush();
        }
    }

    private function generateSlug(Property $property): string
    {
        $slug = $this->slugger->slug($property->getTitleBg())->lower();
        
        // Проверка за уникалност на slug
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $property->getId())) {
            $slug = $originalSlug . '-' . $counter++;
        }
        
        return $slug;
    }

    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Property::class, 'p')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug);

        if ($excludeId) {
            $qb->andWhere('p.id != :id')
               ->setParameter('id', $excludeId);
        }

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }
} 