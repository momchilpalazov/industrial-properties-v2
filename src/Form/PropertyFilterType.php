<?php

namespace App\Form;

use App\Entity\PropertyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Property;
use Doctrine\ORM\EntityRepository;

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
            ->add('type', EntityType::class, [
                'class' => PropertyType::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Всички типове',
                'label' => 'Тип имот'
            ])
            ->add('min_price', NumberType::class, [
                'required' => false,
                'label' => 'Минимална цена',
                'attr' => [
                    'placeholder' => 'Минимална цена'
                ]
            ])
            ->add('max_price', NumberType::class, [
                'required' => false,
                'label' => 'Максимална цена',
                'attr' => [
                    'placeholder' => 'Максимална цена'
                ]
            ])
            ->add('min_area', NumberType::class, [
                'required' => false,
                'label' => 'Минимална площ',
                'attr' => [
                    'placeholder' => 'Минимална площ'
                ]
            ])
            ->add('max_area', NumberType::class, [
                'required' => false,
                'label' => 'Максимална площ',
                'attr' => [
                    'placeholder' => 'Максимална площ'
                ]
            ])
            ->add('location', TextType::class, [
                'required' => false,
                'label' => 'Локация',
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
                'required' => false,
                'attr' => ['class' => 'form-select']
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