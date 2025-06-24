<?php

namespace App\Form\Admin;

use App\Entity\Faq;
use App\Entity\FaqCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FaqType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('questionBg', TextType::class, [
                'label' => 'Въпрос (БГ)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('questionEn', TextType::class, [
                'label' => 'Въпрос (EN)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('questionDe', TextType::class, [
                'label' => 'Въпрос (DE)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('questionRu', TextType::class, [
                'label' => 'Въпрос (RU)',
                'attr' => ['class' => 'form-control']
            ])
            ->add('answerBg', TextareaType::class, [
                'label' => 'Отговор (БГ)',
                'attr' => ['class' => 'form-control ckeditor']
            ])
            ->add('answerEn', TextareaType::class, [
                'label' => 'Отговор (EN)',
                'attr' => ['class' => 'form-control ckeditor']
            ])
            ->add('answerDe', TextareaType::class, [
                'label' => 'Отговор (DE)',
                'attr' => ['class' => 'form-control ckeditor']
            ])
            ->add('answerRu', TextareaType::class, [
                'label' => 'Отговор (RU)',
                'attr' => ['class' => 'form-control ckeditor']
            ])
            ->add('category', EntityType::class, [
                'class' => FaqCategory::class,
                'choice_label' => 'nameBg',
                'label' => 'Категория',
                'placeholder' => 'Изберете категория',
                'attr' => ['class' => 'form-select']
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Активен',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Faq::class,
        ]);
    }
}