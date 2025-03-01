<?php

namespace App\Form;

use App\Entity\PropertyImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PropertyImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Снимка',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Моля качете валидна снимка (JPEG, PNG, GIF или WEBP)',
                        'maxSizeMessage' => 'Снимката не може да бъде по-голяма от {{ limit }}{{ suffix }}'
                    ])
                ]
            ])
            ->add('isMain', CheckboxType::class, [
                'label' => 'Основна снимка',
                'required' => false
            ])
            ->add('is360', CheckboxType::class, [
                'label' => '360° изображение',
                'required' => false
            ])
            ->add('position', HiddenType::class, [
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PropertyImage::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
} 