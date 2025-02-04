<?php

namespace App\Form;

use App\Entity\Property;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PropertyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Заглавие (БГ)',
                'attr' => [
                    'placeholder' => 'Въведете заглавие на български'
                ]
            ])
            ->add('titleEn', TextType::class, [
                'label' => 'Заглавие (EN)',
                'attr' => [
                    'placeholder' => 'Enter title in English'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание (БГ)',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Въведете описание на български'
                ]
            ])
            ->add('descriptionEn', TextareaType::class, [
                'label' => 'Описание (EN)',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Enter description in English'
                ]
            ])
            ->add('location', TextType::class, [
                'label' => 'Локация',
                'attr' => [
                    'placeholder' => 'Въведете локация'
                ]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Цена (EUR)',
                'attr' => [
                    'placeholder' => 'Въведете цена в евро'
                ]
            ])
            ->add('area', NumberType::class, [
                'label' => 'Площ (кв.м)',
                'attr' => [
                    'placeholder' => 'Въведете площ в квадратни метри'
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Тип имот',
                'choices' => [
                    'Склад' => 'warehouse',
                    'Производствено помещение' => 'manufacturing',
                    'Логистичен център' => 'logistics',
                    'Офис сграда' => 'office',
                    'Търговски площи' => 'retail',
                    'Парцел' => 'land'
                ],
                'placeholder' => 'Изберете тип имот'
            ])
            ->add('features', CollectionType::class, [
                'label' => 'Характеристики',
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'required' => false,
                'attr' => [
                    'class' => 'features-collection'
                ]
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => 'Избран имот',
                'required' => false
            ])
            ->add('isAvailable', CheckboxType::class, [
                'label' => 'Наличен',
                'required' => false
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Географска ширина',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Пример: 42.6977',
                    'step' => 'any'
                ],
                'help' => 'Въведете географската ширина в десетичен формат'
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Географска дължина',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Пример: 23.3219',
                    'step' => 'any'
                ],
                'help' => 'Въведете географската дължина в десетичен формат'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Property::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
} 