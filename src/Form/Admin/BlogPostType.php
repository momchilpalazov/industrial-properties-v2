<?php

namespace App\Form\Admin;

use App\Entity\BlogPost;
use App\Repository\BlogPostRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class BlogPostType extends AbstractType
{
    public function __construct(private BlogPostRepository $blogPostRepository)
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titleBg', TextType::class, [
                'label' => 'Заглавие (БГ)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('titleEn', TextType::class, [
                'label' => 'Заглавие (EN)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('excerptBg', TextareaType::class, [
                'label' => 'Кратко описание (БГ)',
                'attr' => ['class' => 'form-control', 'rows' => 3]
            ])
            ->add('excerptEn', TextareaType::class, [
                'label' => 'Кратко описание (EN)',
                'attr' => ['class' => 'form-control', 'rows' => 3]
            ])
            ->add('contentBg', TextareaType::class, [
                'label' => 'Съдържание (БГ)',
                'attr' => ['class' => 'form-control ckeditor']
            ])
            ->add('contentEn', TextareaType::class, [
                'label' => 'Съдържание (EN)',
                'attr' => ['class' => 'form-control ckeditor']
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Категория',
                'choices' => array_flip($this->blogPostRepository->getCategories()),
                'attr' => ['class' => 'form-select']
            ])
            ->add('publishedAt', DateTimeType::class, [
                'label' => 'Дата на публикуване',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Публикувана',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'Език',
                'choices' => [
                    'Български' => 'bg',
                    'English' => 'en',
                    'Deutsch' => 'de',
                    'Русский' => 'ru'
                ],
                'attr' => ['class' => 'form-select']
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