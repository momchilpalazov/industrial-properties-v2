<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PropertyFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Всички типове' => '',
                    'Индустриален терен' => 'industrial_land',
                    'Индустриална сграда' => 'industrial_building',
                    'Логистичен център' => 'logistics_center',
                    'Склад' => 'warehouse',
                    'Производствена база' => 'production_facility'
                ],
                'label' => 'Тип имот',
                'constraints' => [
                    new Assert\Choice([
                        'choices' => [
                            '',
                            'industrial_land',
                            'industrial_building',
                            'logistics_center',
                            'warehouse',
                            'production_facility'
                        ],
                        'message' => 'Моля изберете валиден тип имот'
                    ])
                ]
            ])
            ->add('min_price', NumberType::class, [
                'required' => false,
                'label' => 'Минимална цена',
                'attr' => [
                    'placeholder' => 'Минимална цена'
                ],
                'constraints' => [
                    new Assert\PositiveOrZero(
                        message: 'Цената не може да бъде отрицателна'
                    ),
                    new Assert\LessThan([
                        'value' => 1000000000,
                        'message' => 'Цената не може да бъде повече от {{ compared_value }} €'
                    ])
                ]
            ])
            ->add('max_price', NumberType::class, [
                'required' => false,
                'label' => 'Максимална цена',
                'attr' => [
                    'placeholder' => 'Максимална цена'
                ],
                'constraints' => [
                    new Assert\PositiveOrZero(
                        message: 'Цената не може да бъде отрицателна'
                    ),
                    new Assert\LessThan([
                        'value' => 1000000000,
                        'message' => 'Цената не може да бъде повече от {{ compared_value }} €'
                    ])
                ]
            ])
            ->add('min_area', NumberType::class, [
                'required' => false,
                'label' => 'Минимална площ',
                'attr' => [
                    'placeholder' => 'Минимална площ'
                ],
                'constraints' => [
                    new Assert\PositiveOrZero(
                        message: 'Площта не може да бъде отрицателна'
                    ),
                    new Assert\LessThan([
                        'value' => 1000000,
                        'message' => 'Площта не може да бъде повече от {{ compared_value }} кв.м.'
                    ])
                ]
            ])
            ->add('max_area', NumberType::class, [
                'required' => false,
                'label' => 'Максимална площ',
                'attr' => [
                    'placeholder' => 'Максимална площ'
                ],
                'constraints' => [
                    new Assert\PositiveOrZero(
                        message: 'Площта не може да бъде отрицателна'
                    ),
                    new Assert\LessThan([
                        'value' => 1000000,
                        'message' => 'Площта не може да бъде повече от {{ compared_value }} кв.м.'
                    ])
                ]
            ])
            ->add('location', TextType::class, [
                'required' => false,
                'label' => 'Локация',
                'attr' => [
                    'placeholder' => 'Въведете локация'
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Локацията не може да бъде повече от {{ limit }} символа'
                    ])
                ]
            ])
            ->add('sort', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Най-нови' => 'newest',
                    'Цена (възходящо)' => 'price_asc',
                    'Цена (низходящо)' => 'price_desc',
                    'Площ (възходящо)' => 'area_asc',
                    'Площ (низходящо)' => 'area_desc'
                ],
                'label' => 'Подреди по',
                'constraints' => [
                    new Assert\Choice([
                        'choices' => [
                            'newest',
                            'price_asc',
                            'price_desc',
                            'area_asc',
                            'area_desc'
                        ],
                        'message' => 'Моля изберете валиден критерий за сортиране'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'validation_groups' => ['Default']
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
} 