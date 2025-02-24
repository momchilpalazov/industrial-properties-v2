<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Property;

class PropertyFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'Статус',
                'choices' => [
                    'Свободен' => Property::STATUS_AVAILABLE,
                    'Продаден' => Property::STATUS_SOLD,
                    'Резервиран' => Property::STATUS_RESERVED,
                    'Отдаден под наем' => Property::STATUS_RENTED,
                    'Очаква финализиране' => Property::STATUS_PENDING
                ],
                'placeholder' => 'Всички статуси',
                'required' => false,
                'attr' => ['class' => 'form-select']
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
                'placeholder' => 'Всички типове',
                'required' => false
            ])
            ->add('min_price', NumberType::class, [
                'label' => 'Минимална цена (EUR)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Минимална цена'
                ]
            ])
            ->add('max_price', NumberType::class, [
                'label' => 'Максимална цена (EUR)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Максимална цена'
                ]
            ])
            ->add('min_area', NumberType::class, [
                'label' => 'Минимална площ (кв.м)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Минимална площ'
                ]
            ])
            ->add('max_area', NumberType::class, [
                'label' => 'Максимална площ (кв.м)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Максимална площ'
                ]
            ])
            ->add('location', TextType::class, [
                'label' => 'Локация',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Търсене по локация'
                ]
            ])
            ->add('sort', ChoiceType::class, [
                'label' => 'Подреждане',
                'choices' => [
                    'Най-нови' => 'newest',
                    'Цена (възходящо)' => 'price_asc',
                    'Цена (низходящо)' => 'price_desc',
                    'Площ (възходящо)' => 'area_asc',
                    'Площ (низходящо)' => 'area_desc'
                ],
                'placeholder' => 'Изберете подреждане',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'attr' => [
                'class' => 'property-filter-form'
            ]
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
} 