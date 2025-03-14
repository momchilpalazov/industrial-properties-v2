<?php

namespace App\Controller\Admin;

use App\Entity\PropertyType;
use App\Entity\PropertyCategory;
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
    public function index(PropertyTypeRepository $propertyTypeRepository, EntityManagerInterface $entityManager): Response
    {
        // Извличаме категориите
        $categories = $entityManager->getRepository(PropertyCategory::class)->findBy(
            [], 
            ['position' => 'ASC']
        );
        
        // Извличаме само основните типове имоти (без родител)
        $rootTypes = $propertyTypeRepository->findBy(
            ['parent' => null], 
            ['position' => 'ASC']
        );
        
        return $this->render('admin/property_type/index.html.twig', [
            'categories' => $categories,
            'root_categories' => $rootTypes,
        ]);
    }

    #[Route('/new', name: 'admin_property_type_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $propertyType = new PropertyType();
        
        // Ако имаме parent_id в заявката, задаваме родителя
        if ($parentId = $request->query->get('parent_id')) {
            $parent = $entityManager->getRepository(PropertyType::class)->find($parentId);
            if ($parent) {
                $propertyType->setParent($parent);
                
                // Ако родителят има категория, задаваме същата категория на новия тип
                if ($parent->getCategory()) {
                    $propertyType->setCategory($parent->getCategory());
                }
            }
        }
        
        // Ако имаме category_id в заявката, задаваме категорията
        if ($categoryId = $request->query->get('category_id')) {
            $category = $entityManager->getRepository(PropertyCategory::class)->find($categoryId);
            if ($category) {
                $propertyType->setCategory($category);
            }
        }
        
        $form = $this->createForm(PropertyTypeType::class, $propertyType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Определяме позицията на новата категория
            if ($propertyType->getParent()) {
                // Ако е подкатегория, намираме последната позиция сред децата на родителя
                $lastPosition = $entityManager->getRepository(PropertyType::class)
                    ->createQueryBuilder('pt')
                    ->select('MAX(pt.position)')
                    ->where('pt.parent = :parent')
                    ->setParameter('parent', $propertyType->getParent())
                    ->getQuery()
                    ->getSingleScalarResult();
                
                $propertyType->setPosition($lastPosition ? $lastPosition + 1 : 0);
            } else {
                // Ако е основна категория, намираме последната позиция сред основните категории
                $lastPosition = $entityManager->getRepository(PropertyType::class)
                    ->createQueryBuilder('pt')
                    ->select('MAX(pt.position)')
                    ->where('pt.parent IS NULL')
                    ->getQuery()
                    ->getSingleScalarResult();
                
                $propertyType->setPosition($lastPosition ? $lastPosition + 1 : 0);
            }
            
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
    
    #[Route('/{id}/add-subcategory', name: 'admin_property_type_add_subcategory', methods: ['GET'])]
    public function addSubcategory(PropertyType $propertyType): Response
    {
        return $this->redirectToRoute('admin_property_type_new', [
            'parent_id' => $propertyType->getId()
        ]);
    }
    
    #[Route('/{id}/toggle-visibility', name: 'admin_property_type_toggle_visibility', methods: ['POST'])]
    public function toggleVisibility(Request $request, PropertyType $propertyType, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle-visibility'.$propertyType->getId(), $request->request->get('_token'))) {
            $propertyType->setIsVisible(!$propertyType->isVisible());
            $entityManager->flush();
            
            $status = $propertyType->isVisible() ? 'видим' : 'скрит';
            $this->addFlash('success', "Типът имот е вече {$status}.");
        }
        
        return $this->redirectToRoute('admin_property_type_index');
    }
    
    #[Route('/{id}/move-up', name: 'admin_property_type_move_up', methods: ['POST'])]
    public function moveUp(Request $request, PropertyType $propertyType, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('move-up'.$propertyType->getId(), $request->request->get('_token'))) {
            $currentPosition = $propertyType->getPosition();
            
            if ($currentPosition > 0) {
                // Намираме категорията над текущата
                $qb = $entityManager->createQueryBuilder();
                $qb->select('pt')
                   ->from(PropertyType::class, 'pt')
                   ->where('pt.position < :position')
                   ->andWhere('pt.level = :level');
                
                if ($propertyType->getParent()) {
                    $qb->andWhere('pt.parent = :parent')
                       ->setParameter('parent', $propertyType->getParent());
                } else {
                    $qb->andWhere('pt.parent IS NULL');
                }
                
                $qb->setParameter('position', $currentPosition)
                   ->setParameter('level', $propertyType->getLevel())
                   ->orderBy('pt.position', 'DESC')
                   ->setMaxResults(1);
                
                $previousCategory = $qb->getQuery()->getOneOrNullResult();
                
                if ($previousCategory) {
                    // Разменяме позициите
                    $previousPosition = $previousCategory->getPosition();
                    $previousCategory->setPosition($currentPosition);
                    $propertyType->setPosition($previousPosition);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Позицията на типа имот беше променена успешно.');
                }
            }
        }
        
        return $this->redirectToRoute('admin_property_type_index');
    }
    
    #[Route('/{id}/move-down', name: 'admin_property_type_move_down', methods: ['POST'])]
    public function moveDown(Request $request, PropertyType $propertyType, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('move-down'.$propertyType->getId(), $request->request->get('_token'))) {
            $currentPosition = $propertyType->getPosition();
            
            // Намираме категорията под текущата
            $qb = $entityManager->createQueryBuilder();
            $qb->select('pt')
               ->from(PropertyType::class, 'pt')
               ->where('pt.position > :position')
               ->andWhere('pt.level = :level');
            
            if ($propertyType->getParent()) {
                $qb->andWhere('pt.parent = :parent')
                   ->setParameter('parent', $propertyType->getParent());
            } else {
                $qb->andWhere('pt.parent IS NULL');
            }
            
            $qb->setParameter('position', $currentPosition)
               ->setParameter('level', $propertyType->getLevel())
               ->orderBy('pt.position', 'ASC')
               ->setMaxResults(1);
            
            $nextCategory = $qb->getQuery()->getOneOrNullResult();
            
            if ($nextCategory) {
                // Разменяме позициите
                $nextPosition = $nextCategory->getPosition();
                $nextCategory->setPosition($currentPosition);
                $propertyType->setPosition($nextPosition);
                $entityManager->flush();
                
                $this->addFlash('success', 'Позицията на типа имот беше променена успешно.');
            }
        }
        
        return $this->redirectToRoute('admin_property_type_index');
    }
} 