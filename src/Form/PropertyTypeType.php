<?php

namespace App\Form;

use App\Entity\PropertyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class PropertyTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Име (BG)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Въведете име на български'
                ]
            ])
            ->add('nameEn', TextType::class, [
                'label' => 'Име (EN)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter name in English'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание (BG)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Въведете описание на български'
                ]
            ])
            ->add('descriptionEn', TextareaType::class, [
                'label' => 'Описание (EN)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Enter description in English'
                ]
            ])
            ->add('isVisible', CheckboxType::class, [
                'label' => 'Видим',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'label_attr' => [
                    'class' => 'form-check-label'
                ]
            ])
            ->add('parent', EntityType::class, [
                'class' => PropertyType::class,
                'label' => 'Родителска категория',
                'required' => false,
                'placeholder' => '-- Няма родителска категория --',
                'attr' => [
                    'class' => 'form-select'
                ],
                'choice_label' => function (PropertyType $propertyType) {
                    return $propertyType->getIndentedName();
                },
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('pt')
                        ->orderBy('pt.level', 'ASC')
                        ->addOrderBy('pt.position', 'ASC');
                    
                    // Ако редактираме съществуващ тип, изключваме го и неговите потомци от списъка
                    if (isset($options['data']) && $options['data']->getId()) {
                        $excludedIds = [$options['data']->getId()];
                        
                        // Намираме всички потомци на текущата категория
                        $descendants = $er->createQueryBuilder('child')
                            ->select('child.id')
                            ->where('child.parent = :parent')
                            ->setParameter('parent', $options['data'])
                            ->getQuery()
                            ->getResult();
                        
                        foreach ($descendants as $descendant) {
                            $excludedIds[] = $descendant['id'];
                        }
                        
                        $qb->andWhere('pt.id NOT IN (:excludedIds)')
                           ->setParameter('excludedIds', $excludedIds);
                    }
                    
                    return $qb;
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PropertyType::class,
        ]);
    }
} 