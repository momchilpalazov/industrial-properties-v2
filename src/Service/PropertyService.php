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
    private string $uploadDir;

    public function __construct(
        PropertyRepository $propertyRepository,
        FileUploadService $fileUploadService,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        string $uploadDir
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->fileUploadService = $fileUploadService;
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
        $this->uploadDir = $uploadDir;
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
            if ($image instanceof UploadedFile) {
                $path = $this->fileUploadService->upload($image, 'images/properties');
                if ($path) {
                    $uploadedImages[] = $path;
                }
            }
        }
        return $uploadedImages;
    }

    private function handlePdfUpload(array $file): ?string
    {
        if ($file instanceof UploadedFile) {
            return $this->fileUploadService->upload($file, 'flyers');
        }
        return null;
    }

    public function create(Property $property): void
    {
        // Генериране на slug
        $property->setSlug($this->generateSlug($property));
        
        // Първо записваме имота за да получим ID
        $this->entityManager->persist($property);
        $this->entityManager->flush();

        // След като имаме ID, създаваме директорията за снимки
        $propertyDir = $this->uploadDir . '/images/properties/' . $property->getId();
        try {
            // Създаваме директорията
            if (!is_dir($propertyDir)) {
                mkdir($propertyDir, 0777, true);
                $this->fileUploadService->setFilePermissions($propertyDir);
            }
        } catch (\Exception $e) {
            throw new \Exception('Грешка при създаване на директория за имота: ' . $e->getMessage());
        }
    }

    public function update(Property $property): void
    {
        // Обновяване на slug ако е променено заглавието
        $property->setSlug($this->generateSlug($property));

        $this->entityManager->flush();
    }

    public function delete(Property $property): void
    {
        // Изтриване на всички снимки и директорията на имота
        $this->fileUploadService->removePropertyDirectory('images/properties/' . $property->getId());

        $this->entityManager->remove($property);
        $this->entityManager->flush();
    }

    public function handleImages(Property $property, array $uploadedFiles, ?int $mainImageId = null): void
    {
        try {
            foreach ($uploadedFiles as $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }

                // Проверяваме дали файлът е валиден
                if (!$file->isValid()) {
                    throw new \Exception('Невалиден файл');
                }

                // Създаваме директорията за имота ако не съществува
                $propertyDir = 'public/uploads/images/properties/' . $property->getId();
                if (!is_dir($propertyDir)) {
                    mkdir($propertyDir, 0777, true);
                    $this->fileUploadService->setFilePermissions($propertyDir);
                }
                
                // Качваме файла в директорията на имота
                $filename = $this->fileUploadService->upload($file, 'images/properties/' . $property->getId());

                // Създаваме нов PropertyImage обект
                $image = new PropertyImage();
                $image->setProperty($property);
                $image->setFilename($filename);
                
                // Ако това е първата снимка, я правим основна
                if ($property->getImages()->isEmpty()) {
                    $image->setIsMain(true);
                }

                // Задаваме позиция в края
                $image->setPosition($property->getImages()->count());
                
                $property->addImage($image);
                $this->entityManager->persist($image);
            }

            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \Exception('Грешка при обработка на снимките: ' . $e->getMessage());
        }
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

            // Изтриваме физическия файл
            $this->fileUploadService->remove($image->getFilename(), 'images/properties/' . $property->getId());
            
            // Изтриваме записа от базата данни
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
        // Първо премахваме флага за основна снимка от всички снимки на имота
        foreach ($property->getImages() as $image) {
            $image->setIsMain(false);
        }

        // Намираме избраната снимка и я задаваме като основна
        $mainImage = $property->getImages()->filter(function($image) use ($imageId) {
            return $image->getId() === $imageId;
        })->first();

        if (!$mainImage) {
            throw new \Exception('Image not found');
        }

        $mainImage->setIsMain(true);
        $this->entityManager->flush();
    }

    private function generateSlug(Property $property): string
    {
        $slug = $this->slugger->slug($property->getTitleBg())->lower();
        
        // Проверка за уникалност на slug
        $originalSlug = $slug->toString();
        $counter = 1;
        
        while ($this->slugExists($originalSlug)) {
            $originalSlug = $slug->toString() . '-' . $counter++;
        }
        
        return $originalSlug;
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