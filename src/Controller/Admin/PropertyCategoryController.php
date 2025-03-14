<?php

namespace App\Controller\Admin;

use App\Entity\PropertyCategory;
use App\Repository\PropertyCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/property-categories')]
#[IsGranted('ROLE_ADMIN')]
class PropertyCategoryController extends AbstractController
{
    #[Route('/', name: 'admin_property_category_index', methods: ['GET'])]
    public function index(PropertyCategoryRepository $propertyCategoryRepository): Response
    {
        $categories = $propertyCategoryRepository->findBy([], ['position' => 'ASC']);
        
        return $this->render('admin/property_category/index.html.twig', [
            'categories' => $categories,
        ]);
    }
    
    #[Route('/new', name: 'admin_property_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $category = new PropertyCategory();
        
        $form = $this->createFormBuilder($category)
            ->add('name', null, [
                'label' => 'Име (BG)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Въведете име на български'
                ]
            ])
            ->add('nameEn', null, [
                'label' => 'Име (EN)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter name in English'
                ]
            ])
            ->add('description', null, [
                'label' => 'Описание (BG)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Въведете описание на български'
                ]
            ])
            ->add('descriptionEn', null, [
                'label' => 'Описание (EN)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Enter description in English'
                ]
            ])
            ->add('isVisible', null, [
                'label' => 'Видима',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'label_attr' => [
                    'class' => 'form-check-label'
                ]
            ])
            ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Генерираме slug от името
            $slug = $slugger->slug(strtolower($category->getName()))->toString();
            $category->setSlug($slug);
            
            // Определяме позицията на новата категория
            $lastPosition = $entityManager->getRepository(PropertyCategory::class)
                ->createQueryBuilder('pc')
                ->select('MAX(pc.position)')
                ->getQuery()
                ->getSingleScalarResult();
            
            $category->setPosition($lastPosition ? $lastPosition + 1 : 0);
            
            $entityManager->persist($category);
            $entityManager->flush();
            
            $this->addFlash('success', 'Категорията беше създадена успешно.');
            return $this->redirectToRoute('admin_property_type_index');
        }
        
        return $this->render('admin/property_category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'admin_property_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PropertyCategory $category, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createFormBuilder($category)
            ->add('name', null, [
                'label' => 'Име (BG)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Въведете име на български'
                ]
            ])
            ->add('nameEn', null, [
                'label' => 'Име (EN)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter name in English'
                ]
            ])
            ->add('description', null, [
                'label' => 'Описание (BG)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Въведете описание на български'
                ]
            ])
            ->add('descriptionEn', null, [
                'label' => 'Описание (EN)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Enter description in English'
                ]
            ])
            ->add('isVisible', null, [
                'label' => 'Видима',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'label_attr' => [
                    'class' => 'form-check-label'
                ]
            ])
            ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Обновяваме slug от името, ако е променено
            $slug = $slugger->slug(strtolower($category->getName()))->toString();
            $category->setSlug($slug);
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Категорията беше редактирана успешно.');
            return $this->redirectToRoute('admin_property_type_index');
        }
        
        return $this->render('admin/property_category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }
    
    #[Route('/{id}', name: 'admin_property_category_delete', methods: ['POST'])]
    public function delete(Request $request, PropertyCategory $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            // Проверка дали има свързани типове имоти
            if (!$category->getPropertyTypes()->isEmpty()) {
                $this->addFlash('error', 'Не можете да изтриете тази категория, защото има свързани типове имоти с нея.');
                return $this->redirectToRoute('admin_property_type_index');
            }
            
            // Проверка дали има свързани имоти
            if (!$category->getProperties()->isEmpty()) {
                $this->addFlash('error', 'Не можете да изтриете тази категория, защото има свързани имоти с нея.');
                return $this->redirectToRoute('admin_property_type_index');
            }
            
            $entityManager->remove($category);
            $entityManager->flush();
            
            $this->addFlash('success', 'Категорията беше изтрита успешно.');
        }
        
        return $this->redirectToRoute('admin_property_type_index');
    }
    
    #[Route('/{id}/toggle-visibility', name: 'admin_property_category_toggle_visibility', methods: ['POST'])]
    public function toggleVisibility(Request $request, PropertyCategory $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle-visibility'.$category->getId(), $request->request->get('_token'))) {
            $category->setIsVisible(!$category->isVisible());
            $entityManager->flush();
            
            $status = $category->isVisible() ? 'видима' : 'скрита';
            $this->addFlash('success', "Категорията е вече {$status}.");
        }
        
        return $this->redirectToRoute('admin_property_type_index');
    }
    
    #[Route('/{id}/move-up', name: 'admin_property_category_move_up', methods: ['POST'])]
    public function moveUp(Request $request, PropertyCategory $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('move-up'.$category->getId(), $request->request->get('_token'))) {
            $currentPosition = $category->getPosition();
            
            if ($currentPosition > 0) {
                // Намираме категорията над текущата
                $previousCategory = $entityManager->getRepository(PropertyCategory::class)
                    ->createQueryBuilder('pc')
                    ->where('pc.position < :position')
                    ->setParameter('position', $currentPosition)
                    ->orderBy('pc.position', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();
                
                if ($previousCategory) {
                    // Разменяме позициите
                    $previousPosition = $previousCategory->getPosition();
                    $previousCategory->setPosition($currentPosition);
                    $category->setPosition($previousPosition);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Позицията на категорията беше променена успешно.');
                }
            }
        }
        
        return $this->redirectToRoute('admin_property_type_index');
    }
    
    #[Route('/{id}/move-down', name: 'admin_property_category_move_down', methods: ['POST'])]
    public function moveDown(Request $request, PropertyCategory $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('move-down'.$category->getId(), $request->request->get('_token'))) {
            $currentPosition = $category->getPosition();
            
            // Намираме категорията под текущата
            $nextCategory = $entityManager->getRepository(PropertyCategory::class)
                ->createQueryBuilder('pc')
                ->where('pc.position > :position')
                ->setParameter('position', $currentPosition)
                ->orderBy('pc.position', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
            
            if ($nextCategory) {
                // Разменяме позициите
                $nextPosition = $nextCategory->getPosition();
                $nextCategory->setPosition($currentPosition);
                $category->setPosition($nextPosition);
                $entityManager->flush();
                
                $this->addFlash('success', 'Позицията на категорията беше променена успешно.');
            }
        }
        
        return $this->redirectToRoute('admin_property_type_index');
    }
} 