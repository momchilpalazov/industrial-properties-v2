<?php

namespace App\Form\Admin;

use App\Entity\BlogPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class BlogPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Заглавие',
                'attr' => [
                    'placeholder' => 'Въведете заглавие'
                ]
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Съдържание',
                'config' => [
                    'toolbar' => 'full',
                    'language' => 'bg'
                ]
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'Език',
                'choices' => [
                    'Български' => 'bg',
                    'English' => 'en'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Статус',
                'choices' => [
                    'Чернова' => 'draft',
                    'Публикувана' => 'published'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogPost::class,
        ]);
    }
} 