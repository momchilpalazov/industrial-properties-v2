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
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class PropertyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('referenceNumber', TextType::class, [
                'label' => 'Референтен номер',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ще се генерира автоматично ако е празно'
                ]
            ])
            ->add('titleBg', TextType::class, [
                'label' => 'Заглавие (BG)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('titleEn', TextType::class, [
                'label' => 'Заглавие (EN)',
                'attr' => ['class' => 'form-control'],
                'required' => false
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
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 10
                ]
            ])
            ->add('descriptionEn', TextareaType::class, [
                'label' => 'Описание (EN)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 10
                ]
            ])
            ->add('descriptionDe', TextareaType::class, [
                'label' => 'Описание (DE)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 10
                ]
            ])
            ->add('descriptionRu', TextareaType::class, [
                'label' => 'Описание (RU)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 10
                ]
            ])
            ->add('locationBg', TextType::class, [
                'label' => 'Локация (BG)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('locationEn', TextType::class, [
                'label' => 'Локация (EN)',
                'attr' => ['class' => 'form-control'],
                'required' => false
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
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('price', NumberType::class, [
                'label' => 'Цена (€)',
                'required' => false,
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
            ->add('status', ChoiceType::class, [
                'label' => 'Статус',
                'choices' => [
                    'Свободен' => Property::STATUS_AVAILABLE,
                    'Продаден' => Property::STATUS_SOLD,
                    'Резервиран' => Property::STATUS_RESERVED,
                    'Отдаден под наем' => Property::STATUS_RENTED,
                    'Очаква финализиране' => Property::STATUS_PENDING
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