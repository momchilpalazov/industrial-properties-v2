<?php

namespace App\Form\Admin;

use App\Entity\BlogCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogCategoryType extends AbstractType
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
            ->add('nameDe', TextType::class, [
                'label' => 'Име (DE)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Namen auf Deutsch eingeben'
                ]
            ])
            ->add('nameRu', TextType::class, [
                'label' => 'Име (RU)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Введите название на русском'
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
            ->add('descriptionDe', TextareaType::class, [
                'label' => 'Описание (DE)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Beschreibung auf Deutsch eingeben'
                ]
            ])
            ->add('descriptionRu', TextareaType::class, [
                'label' => 'Описание (RU)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Введите описание на русском'
                ]
            ])
            ->add('slug', TextType::class, [
                'label' => 'URL адрес (slug)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Оставете празно за автоматично генериране'
                ]
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Позиция',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ]
            ])
            ->add('isVisible', CheckboxType::class, [
                'label' => 'Видима',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'label_attr' => [
                    'class' => 'form-check-label'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogCategory::class,
        ]);
    }
}
