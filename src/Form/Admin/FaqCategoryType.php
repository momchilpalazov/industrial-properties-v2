<?php

namespace App\Form\Admin;

use App\Entity\FaqCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FaqCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nameBg', TextType::class, [
                'label' => 'Име (BG)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Въведете име на български'
                ]
            ])
            ->add('nameEn', TextType::class, [
                'label' => 'Име (EN)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter name in English'
                ]
            ])
            ->add('nameDe', TextType::class, [
                'label' => 'Име (DE)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Namen auf Deutsch eingeben'
                ]
            ])
            ->add('nameRu', TextType::class, [
                'label' => 'Име (RU)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Введите название на русском'
                ]
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug (URL)',
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
            'data_class' => FaqCategory::class,
        ]);
    }
}