<?php

namespace App\Form\Admin;

use App\Entity\Property;
use App\Entity\PropertyType as PropertyTypeEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
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
            ->add('descriptionBg', CKEditorType::class, [
                'label' => 'Описание (BG)',
                'attr' => ['rows' => 5],
                'config' => [
                    'language' => 'bg'
                ]
            ])
            ->add('descriptionEn', CKEditorType::class, [
                'label' => 'Описание (EN)',
                'required' => false,
                'attr' => ['rows' => 5],
                'config' => [
                    'language' => 'en'
                ]
            ])
            ->add('descriptionDe', CKEditorType::class, [
                'label' => 'Описание (DE)',
                'required' => false,
                'attr' => ['rows' => 5],
                'config' => [
                    'language' => 'de'
                ]
            ])
            ->add('descriptionRu', CKEditorType::class, [
                'label' => 'Описание (RU)',
                'required' => false,
                'attr' => ['rows' => 5],
                'config' => [
                    'language' => 'ru'
                ]
            ])
            ->add('locationBg', TextType::class, [
                'label' => 'Локация (БГ)',
                'attr' => [
                    'placeholder' => 'Въведете локация на български',
                    'class' => 'form-control'
                ]
            ])
            ->add('locationEn', TextType::class, [
                'label' => 'Локация (EN)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter location in English',
                    'class' => 'form-control'
                ]
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
            ->add('address', TextType::class, [
                'label' => 'Точен адрес',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Въведете точен адрес',
                    'class' => 'form-control'
                ]
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
            ->add('type', EntityType::class, [
                'class' => PropertyTypeEntity::class,
                'choice_label' => 'name',
                'label' => 'Тип имот',
                'placeholder' => 'Изберете тип имот',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('pt')
                        ->orderBy('pt.name', 'ASC');
                },
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
                'label' => 'Избран имот',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Географска ширина',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Пример: 42.6977',
                    'step' => 'any',
                    'class' => 'form-control'
                ],
                'help' => 'Въведете географската ширина в десетичен формат'
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Географска дължина',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Пример: 23.3219',
                    'step' => 'any',
                    'class' => 'form-control'
                ],
                'help' => 'Въведете географската дължина в десетичен формат'
            ])
            ->add('isVip', CheckboxType::class, [
                'label' => 'VIP имот',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'data-toggle' => 'vip-options'
                ]
            ])
            ->add('vipDuration', ChoiceType::class, [
                'label' => 'Продължителност на VIP статус',
                'mapped' => false,
                'required' => false,
                'choices' => [
                    '7 дни' => 7,
                    '14 дни' => 14,
                    '30 дни' => 30
                ],
                'attr' => [
                    'class' => 'form-select vip-option',
                ],
                'row_attr' => [
                    'class' => 'vip-options-group'
                ]
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