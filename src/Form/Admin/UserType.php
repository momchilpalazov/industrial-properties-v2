<?php

namespace App\Form\Admin;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Име',
                'attr' => [
                    'placeholder' => 'Въведете име'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Имейл',
                'attr' => [
                    'placeholder' => 'Въведете имейл'
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Парола',
                'mapped' => false,
                'required' => $options['is_new'],
                'attr' => [
                    'placeholder' => 'Въведете парола'
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Роли',
                'choices' => [
                    'Администратор' => 'ROLE_ADMIN',
                    'Потребител' => 'ROLE_USER'
                ],
                'multiple' => true,
                'expanded' => true
            ])
            ->add('isActive', ChoiceType::class, [
                'label' => 'Статус',
                'choices' => [
                    'Активен' => true,
                    'Неактивен' => false
                ],
                'expanded' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_new' => false
        ]);
    }
} 