<?php

namespace App\Form;

use App\Entity\PropertyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PropertyTypeType extends AbstractType
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PropertyType::class,
        ]);
    }
} 