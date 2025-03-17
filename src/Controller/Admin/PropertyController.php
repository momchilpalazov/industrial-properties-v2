<?php

namespace App\Controller\Admin;

use App\Entity\Property;
use App\Entity\PropertyImage;
use App\Entity\PropertyPdf;
use App\Form\Admin\PropertyType;
use App\Repository\PropertyRepository;
use App\Repository\PropertyTypeRepository;
use App\Service\FileUploadService;
use App\Service\PropertyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/properties')]
#[IsGranted('ROLE_ADMIN')]
class PropertyController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PropertyRepository $propertyRepository,
        private PropertyTypeRepository $propertyTypeRepository,
        private PropertyService $propertyService,
        private FileUploadService $fileUploadService,
        private PaginatorInterface $paginator,
        private ParameterBagInterface $params,
        private ValidatorInterface $validator
    ) {}

    #[Route('/', name: 'admin_property_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $queryBuilder = $this->propertyRepository->createQueryBuilder('p')
            ->leftJoin('p.type', 'pt')
            ->orderBy('p.createdAt', 'DESC');

        // Прилагане на филтрите
        if ($type = $request->query->get('type')) {
            $queryBuilder->andWhere('pt.id = :type')
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
            10
        );

        return $this->render('admin/property/index.html.twig', [
            'properties' => $pagination,
            'property_types' => $this->propertyTypeRepository->findAll()
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
            'here_maps_api_key' => $this->getParameter('app.here_maps_api_key')
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_property_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Property $property): Response
    {
        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Обработка на VIP статуса
            if ($form->has('vipDuration') && $form->get('isVip')->getData()) {
                $days = (int) $form->get('vipDuration')->getData();
                if ($days > 0) {
                    $property->setIsVip(true);
                    $property->setVipExpiration(new \DateTimeImmutable("+{$days} days"));
                }
            } else {
                $property->setIsVip(false);
                $property->setVipExpiration(null);
            }

            // Обработка на PDF expose
            $file = $form->get('expose')->get('exposeFile')->getData();
            if ($file) {
                try {
                    // Проверяваме дали файлът е валиден
                    if (!$file->isValid()) {
                        throw new \Exception('Невалиден PDF файл');
                    }

                    // Проверяваме дали директорията съществува
                    $uploadDir = $this->params->get('property_expose_directory');
                    if (!is_dir($uploadDir)) {
                        if (!mkdir($uploadDir, 0777, true)) {
                            throw new \Exception('Грешка при създаване на директорията за PDF файлове');
                        }
                        $this->fileUploadService->setFilePermissions($uploadDir);
                    }

                    // Проверяваме дали директорията е записваема
                    if (!is_writable($uploadDir)) {
                        throw new \Exception('Директорията за PDF файлове не е записваема');
                    }

                    // Генерираме уникално име за файла
                    $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                    
                    // Проверяваме дали разширението е PDF
                    if ($file->getClientOriginalExtension() !== 'pdf') {
                        throw new \Exception('Файлът трябва да бъде PDF формат');
                    }
                    
                    // Проверяваме размера на файла
                    $maxSize = 20 * 1024 * 1024; // 20MB
                    if ($file->getSize() > $maxSize) {
                        throw new \Exception('Файлът е твърде голям. Максималният размер е 20MB');
                    }
                    
                    // Преместваме файла
                    try {
                        $file->move($uploadDir, $filename);
                    } catch (\Exception $e) {
                        throw new \Exception('Грешка при преместване на файла: ' . $e->getMessage());
                    }
                    
                    // Задаваме правилни права на файла
                    $this->fileUploadService->setFilePermissions($uploadDir . '/' . $filename);

                    // Създаваме нов PDF запис
                    $pdf = new PropertyPdf();
                    $pdf->setFilename($filename);
                    $pdf->setProperty($property);
                    
                    // Проверяваме дали PDF обектът е валиден
                    $errors = $this->validator->validate($pdf);
                    if (count($errors) > 0) {
                        throw new \Exception((string) $errors);
                    }

                    // Опитваме се да запазим PDF файла
                    try {
                        $this->entityManager->persist($pdf);
                        $this->entityManager->flush();
                        $this->addFlash('success', 'PDF файлът беше качен успешно.');
                    } catch (\Exception $e) {
                        throw new \Exception('Грешка при запазване в базата данни: ' . $e->getMessage());
                    }
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Грешка при качване на PDF файла: ' . $e->getMessage());
                    // Изтриваме файла ако е бил качен
                    if (isset($filename) && file_exists($uploadDir . '/' . $filename)) {
                        unlink($uploadDir . '/' . $filename);
                    }
                    return $this->render('admin/property/edit.html.twig', [
                        'property' => $property,
                        'form' => $form,
                        'here_maps_api_key' => $this->getParameter('app.here_maps_api_key')
                    ]);
                }
            }

            try {
                $this->propertyService->update($property);
                $this->addFlash('success', 'Имотът беше успешно редактиран.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Грешка при запазване на промените: ' . $e->getMessage());
            }

            return $this->redirectToRoute('admin_property_show', ['id' => $property->getId()]);
        }

        return $this->render('admin/property/edit.html.twig', [
            'property' => $property,
            'form' => $form,
            'here_maps_api_key' => $this->getParameter('app.here_maps_api_key')
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

    #[Route('/{id}/toggle-active', name: 'admin_property_toggle_active', methods: ['POST'])]
    public function toggleActive(Property $property): Response
    {
        $property->setIsActive(!$property->isActive());
        $this->propertyRepository->save($property, true);

        return $this->json([
            'success' => true,
            'active' => $property->isActive(),
            'message' => $property->isActive() ? 'Имотът е активиран' : 'Имотът е деактивиран'
        ]);
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
        if (!$this->isCsrfTokenValid('upload', $request->request->get('_token'))) {
            return $this->json(['success' => false, 'error' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        $file = $request->files->get('file');
        $is360 = $request->request->get('is360') === 'true';
        
        if (!$file) {
            return $this->json(['success' => false, 'error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Използваме PropertyService за качване на снимката
            $this->propertyService->handleImages($property, [$file], null, $is360);
            
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
                    'path' => '/uploads/images/properties/' . $property->getId() . '/' . $lastImage->getFilename(),
                    'is360' => $lastImage->is360()
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

    #[Route('/{id}/delete-pdf/{pdfId}', name: 'admin_property_delete_pdf', methods: ['POST'])]
    public function deletePdf(Property $property, int $pdfId): Response
    {
        try {
            $pdf = $this->entityManager->getRepository(PropertyPdf::class)->find($pdfId);
            
            if (!$pdf || $pdf->getProperty() !== $property) {
                throw new \Exception('PDF файлът не беше намерен');
            }

            // Изтриваме физическия файл
            $uploadDir = $this->params->get('property_expose_directory');
            $filePath = $uploadDir . '/' . $pdf->getFilename();
            
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Изтриваме записа от базата данни
            $this->entityManager->remove($pdf);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'PDF файлът беше изтрит успешно'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 