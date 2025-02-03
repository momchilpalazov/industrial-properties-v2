<?php

namespace App\Form\Admin;

use App\Entity\Property;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('titleBg', TextType::class, [
                'label' => 'Заглавие (BG)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('titleEn', TextType::class, [
                'label' => 'Заглавие (EN)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('titleDe', TextType::class, [
                'label' => 'Заглавие (DE)',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('titleRu', TextType::class, [
                'label' => 'Заглавие (RU)',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('descriptionBg', TextareaType::class, [
                'label' => 'Описание (BG)',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5
                ]
            ])
            ->add('descriptionEn', TextareaType::class, [
                'label' => 'Описание (EN)',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5
                ]
            ])
            ->add('descriptionDe', TextareaType::class, [
                'label' => 'Описание (DE)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5
                ]
            ])
            ->add('descriptionRu', TextareaType::class, [
                'label' => 'Описание (RU)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5
                ]
            ])
            ->add('locationBg', TextType::class, [
                'label' => 'Локация (BG)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('locationEn', TextType::class, [
                'label' => 'Локация (EN)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('locationDe', TextType::class, [
                'label' => 'Локация (DE)',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('locationRu', TextType::class, [
                'label' => 'Локация (RU)',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('area', NumberType::class, [
                'label' => 'Площ (м²)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('price', NumberType::class, [
                'label' => 'Цена (€)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Тип имот',
                'choices' => [
                    'Индустриален терен' => 'industrial_land',
                    'Индустриална сграда' => 'industrial_building',
                    'Логистичен център' => 'logistics_center',
                    'Склад' => 'warehouse',
                    'Производствена база' => 'production_facility'
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Активен',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => 'Featured',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Property::class,
        ]);
    }
} 