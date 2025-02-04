<?php

namespace App\Controller\Admin;

use App\Entity\Property;
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

#[Route('/admin/properties')]
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
        $properties = $this->propertyRepository->findBy([], ['createdAt' => 'DESC']);

        $pagination = $this->paginator->paginate(
            $properties,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/property/index.html.twig', [
            'properties' => $pagination,
        ]);
    }

    #[Route('/new', name: 'admin_property_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $property = new Property();

        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->propertyRepository->save($property);

            $this->addFlash('success', 'Имотът беше създаден успешно');

            return $this->redirectToRoute('admin_property_index');
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
            $this->propertyRepository->save($property);

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
            $this->propertyRepository->remove($property);
            $this->addFlash('success', 'Имотът беше изтрит успешно');
        }

        return $this->redirectToRoute('admin_property_index');
    }

    #[Route('/{id}/toggle-featured', name: 'admin_property_toggle_featured', methods: ['POST'])]
    public function toggleFeatured(Property $property): Response
    {
        $property->setIsFeatured(!$property->isFeatured());
        $this->propertyRepository->save($property);

        return $this->json(['featured' => $property->isFeatured()]);
    }

    #[Route('/{id}/toggle-available', name: 'admin_property_toggle_available', methods: ['POST'])]
    public function toggleAvailable(Property $property): Response
    {
        $property->setIsAvailable(!$property->isAvailable());
        $this->propertyRepository->save($property);

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
        $this->propertyService->deleteImage($property, $imageId);
        
        return $this->json([
            'success' => true
        ]);
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
} 