<?php

namespace App\Form;

use App\Entity\Promotion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Тип промоция',
                'choices' => [
                    'VIP обява' => 'vip',
                    'Избрана обява' => 'featured'
                ],
                'required' => true,
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Начална дата',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Крайна дата',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Цена',
                'currency' => 'EUR',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
        ]);
    }
} 