<?php

namespace App\Form;

use App\Entity\PropertyInquiry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PropertyInquiryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'property.inquiry.name',
                'attr' => [
                    'placeholder' => 'property.inquiry.name_placeholder'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'property.inquiry.email',
                'attr' => [
                    'placeholder' => 'property.inquiry.email_placeholder'
                ]
            ])
            ->add('phone', TelType::class, [
                'label' => 'property.inquiry.phone',
                'required' => false,
                'attr' => [
                    'placeholder' => 'property.inquiry.phone_placeholder'
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'property.inquiry.message',
                'attr' => [
                    'placeholder' => 'property.inquiry.message_placeholder',
                    'rows' => 5
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PropertyInquiry::class,
            'translation_domain' => 'messages'
        ]);
    }
} 