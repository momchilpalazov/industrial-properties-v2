<?php

namespace App\Controller\Admin;

use App\Entity\PropertyType;
use App\Form\PropertyTypeType;
use App\Repository\PropertyTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/property-types')]
#[IsGranted('ROLE_ADMIN')]
class PropertyTypeController extends AbstractController
{
    #[Route('/', name: 'admin_property_type_index', methods: ['GET'])]
    public function index(PropertyTypeRepository $propertyTypeRepository): Response
    {
        // Извличаме всички основни категории (без родител)
        $mainCategories = $propertyTypeRepository->findBy(
            ['parent' => null], 
            ['name' => 'ASC']
        );
        
        // Създаваме структурирани данни за шаблона
        $structuredCategories = [];
        
        foreach ($mainCategories as $mainCategory) {
            // Добавяме основната категория
            $structuredCategories[] = [
                'category' => $mainCategory,
                'level' => 0
            ];
            
            // Добавяме нейните подкатегории (ако има)
            $subCategories = $propertyTypeRepository->findBy(
                ['parent' => $mainCategory->getId()],
                ['name' => 'ASC']
            );
            
            foreach ($subCategories as $subCategory) {
                $structuredCategories[] = [
                    'category' => $subCategory,
                    'level' => 1
                ];
            }
        }
        
        return $this->render('admin/property_type/index.html.twig', [
            'structured_categories' => $structuredCategories,
        ]);
    }

    #[Route('/new', name: 'admin_property_type_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $propertyType = new PropertyType();
        $form = $this->createForm(PropertyTypeType::class, $propertyType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($propertyType);
            $entityManager->flush();

            $this->addFlash('success', 'Типът имот беше създаден успешно.');
            return $this->redirectToRoute('admin_property_type_index');
        }

        return $this->render('admin/property_type/new.html.twig', [
            'property_type' => $propertyType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_property_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PropertyType $propertyType, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PropertyTypeType::class, $propertyType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Типът имот беше редактиран успешно.');
            return $this->redirectToRoute('admin_property_type_index');
        }

        return $this->render('admin/property_type/edit.html.twig', [
            'property_type' => $propertyType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_property_type_delete', methods: ['POST'])]
    public function delete(Request $request, PropertyType $propertyType, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$propertyType->getId(), $request->request->get('_token'))) {
            // Проверка дали има свързани имоти
            if (!$propertyType->getProperties()->isEmpty()) {
                $this->addFlash('error', 'Не можете да изтриете този тип имот, защото има свързани имоти с него.');
                return $this->redirectToRoute('admin_property_type_index');
            }
            
            // Проверка дали има подкатегории
            if (!$propertyType->getChildren()->isEmpty()) {
                $this->addFlash('error', 'Не можете да изтриете този тип имот, защото има подкатегории свързани с него.');
                return $this->redirectToRoute('admin_property_type_index');
            }

            $entityManager->remove($propertyType);
            $entityManager->flush();

            $this->addFlash('success', 'Типът имот беше изтрит успешно.');
        }

        return $this->redirectToRoute('admin_property_type_index');
    }
} 