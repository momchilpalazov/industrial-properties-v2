<?php

namespace App\Controller\Admin;

use App\Entity\Property;
use App\Entity\PropertyImage;
use App\Form\Admin\PropertyType;
use App\Repository\PropertyRepository;
use App\Service\FileUploadService;
use App\Service\PropertyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/properties')]
#[IsGranted('ROLE_ADMIN')]
class PropertyController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PropertyRepository $propertyRepository,
        private PropertyService $propertyService,
        private FileUploadService $fileUploadService,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/', name: 'admin_property_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $queryBuilder = $this->propertyRepository->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC');

        // Прилагане на филтрите
        if ($type = $request->query->get('type')) {
            $queryBuilder->andWhere('p.type = :type')
                ->setParameter('type', $type);
        }

        if ($location = $request->query->get('location')) {
            $queryBuilder->andWhere('p.locationBg LIKE :location')
                ->setParameter('location', '%' . $location . '%');
        }

        if ($status = $request->query->get('status')) {
            $queryBuilder->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            9 // брой имоти на страница
        );

        return $this->render('admin/property/index.html.twig', [
            'properties' => $pagination,
        ]);
    }

    #[Route('/{id}/show', name: 'admin_property_show', methods: ['GET'])]
    public function show(Property $property): Response
    {
        return $this->render('admin/property/show.html.twig', [
            'property' => $property,
        ]);
    }

    #[Route('/new', name: 'admin_property_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $property = new Property();

        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->propertyService->create($property);
                
                $this->addFlash('success', 'Имотът беше създаден успешно. Сега можете да добавите снимки.');
                
                return $this->redirectToRoute('admin_property_images', ['id' => $property->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Възникна грешка при създаването на имота: ' . $e->getMessage());
            }
        }

        return $this->render('admin/property/new.html.twig', [
            'property' => $property,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_property_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Property $property): Response
    {
        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->propertyRepository->save($property, true);

            $this->addFlash('success', 'Имотът беше редактиран успешно');

            return $this->redirectToRoute('admin_property_index');
        }

        return $this->render('admin/property/edit.html.twig', [
            'property' => $property,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_property_delete', methods: ['POST'])]
    public function delete(Request $request, Property $property): Response
    {
        if ($this->isCsrfTokenValid('delete'.$property->getId(), $request->request->get('_token'))) {
            try {
                $this->propertyService->delete($property);
                $this->addFlash('success', 'Имотът беше изтрит успешно');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Възникна грешка при изтриването на имота: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('admin_property_index');
    }

    #[Route('/{id}/toggle-featured', name: 'admin_property_toggle_featured', methods: ['POST'])]
    public function toggleFeatured(Property $property): Response
    {
        $property->setIsFeatured(!$property->isFeatured());
        $this->propertyRepository->save($property, true);

        return $this->json(['featured' => $property->isFeatured()]);
    }

    #[Route('/{id}/toggle-available', name: 'admin_property_toggle_available', methods: ['POST'])]
    public function toggleAvailable(Property $property): Response
    {
        $property->setIsAvailable(!$property->isAvailable());
        $this->propertyRepository->save($property, true);

        return $this->json(['available' => $property->isAvailable()]);
    }

    #[Route('/{id}/images', name: 'admin_property_images', methods: ['GET', 'POST'])]
    public function manageImages(Request $request, Property $property): Response
    {
        if ($request->isMethod('POST')) {
            $images = $request->files->get('images');
            $mainImage = $request->request->get('main_image');
            
            if ($images) {
                $this->propertyService->handleImages($property, $images, $mainImage);
                $this->addFlash('success', 'Снимките бяха обновени успешно.');
            }
            
            return $this->redirectToRoute('admin_property_images', ['id' => $property->getId()]);
        }

        return $this->render('admin/property/images.html.twig', [
            'property' => $property
        ]);
    }

    #[Route('/{id}/delete-image/{imageId}', name: 'admin_property_delete_image', methods: ['POST'])]
    public function deleteImage(Property $property, int $imageId): Response
    {
        try {
            $this->propertyService->deleteImage($property, $imageId);
            return $this->json([
                'success' => true,
                'message' => 'Снимката беше изтрита успешно'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/reorder-images', name: 'admin_property_reorder_images', methods: ['POST'])]
    public function reorderImages(Request $request, Property $property): Response
    {
        $positions = json_decode($request->getContent(), true);
        $this->propertyService->reorderImages($property, $positions);
        
        return $this->json([
            'success' => true
        ]);
    }

    #[Route('/{id}/image/upload', name: 'admin_property_image_upload', methods: ['POST'])]
    public function uploadImage(Request $request, Property $property): Response
    {
        $file = $request->files->get('file');
        
        if (!$file) {
            return $this->json(['success' => false, 'error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Използваме PropertyService за качване на снимката
            $this->propertyService->handleImages($property, [$file]);
            
            // Взимаме последно качената снимка
            $lastImage = $property->getImages()->last();
            
            if (!$lastImage) {
                throw new \Exception('Грешка при запазване на изображението');
            }
            
            return $this->json([
                'success' => true,
                'image' => [
                    'id' => $lastImage->getId(),
                    'filename' => $lastImage->getFilename(),
                    'path' => $this->fileUploadService->getPublicPath($lastImage->getFilename(), $property->getId())
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/image/{imageId}/set-main', name: 'admin_property_image_set_main', methods: ['POST'])]
    public function setMainImage(Property $property, int $imageId): Response
    {
        try {
            $this->propertyService->setMainImage($property, $imageId);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 