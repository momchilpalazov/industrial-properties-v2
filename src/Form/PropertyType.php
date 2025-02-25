<?php

namespace App\Form;

use App\Entity\Property;
use App\Entity\PropertyType as PropertyTypeEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class PropertyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titleBg', TextType::class, [
                'label' => 'Заглавие (БГ)',
                'attr' => [
                    'placeholder' => 'Въведете заглавие на български'
                ]
            ])
            ->add('titleEn', TextType::class, [
                'label' => 'Заглавие (EN)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter title in English'
                ]
            ])
            ->add('descriptionBg', TextareaType::class, [
                'label' => 'Описание (БГ)',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Въведете описание на български'
                ]
            ])
            ->add('descriptionEn', TextareaType::class, [
                'label' => 'Описание (EN)',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Enter description in English'
                ]
            ])
            ->add('locationBg', TextType::class, [
                'label' => 'Локация (БГ)',
                'attr' => [
                    'placeholder' => 'Въведете локация на български'
                ]
            ])
            ->add('locationEn', TextType::class, [
                'label' => 'Локация (EN)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter location in English'
                ]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Цена (EUR)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Въведете цена в евро'
                ]
            ])
            ->add('area', NumberType::class, [
                'label' => 'Площ (кв.м)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Въведете площ в квадратни метри'
                ]
            ])
            ->add('type', EntityType::class, [
                'class' => PropertyTypeEntity::class,
                'choice_label' => 'name',
                'label' => 'Тип имот',
                'placeholder' => 'Изберете тип имот',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('pt')
                        ->orderBy('pt.name', 'ASC');
                }
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Статус',
                'choices' => [
                    'Наличен' => Property::STATUS_AVAILABLE,
                    'Продаден' => Property::STATUS_SOLD,
                    'Резервиран' => Property::STATUS_RESERVED,
                    'Под наем' => Property::STATUS_RENTED,
                    'В процес' => Property::STATUS_PENDING
                ],
                'placeholder' => 'Изберете статус'
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Активен',
                'required' => false
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => 'Избран имот',
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