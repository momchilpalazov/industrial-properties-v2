<?php

namespace App\Form;

use App\Entity\Offer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerName', TextType::class, [
                'label' => 'Име на клиента'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Имейл'
            ])
            ->add('phone', TelType::class, [
                'label' => 'Телефон'
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Цена',
                'currency' => 'EUR',
                'scale' => 2
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Статус',
                'choices' => [
                    'Чернова' => 'чернова',
                    'Изпратена' => 'изпратена',
                    'Приета' => 'приета',
                    'Отказана' => 'отказана'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offer::class,
        ]);
    }
} 