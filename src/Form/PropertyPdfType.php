<?php

namespace App\Form;

use App\Entity\PropertyPdf;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PropertyPdfType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'PDF файл',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf'
                        ],
                        'mimeTypesMessage' => 'Моля качете валиден PDF файл',
                        'maxSizeMessage' => 'Файлът не може да бъде по-голям от {{ limit }}{{ suffix }}'
                    ])
                ]
            ])
            ->add('title', TextType::class, [
                'label' => 'Заглавие (БГ)',
                'attr' => [
                    'placeholder' => 'Въведете заглавие на български'
                ]
            ])
            ->add('titleEn', TextType::class, [
                'label' => 'Заглавие (EN)',
                'attr' => [
                    'placeholder' => 'Enter title in English'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PropertyPdf::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
} 