<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('payment_gateway', ChoiceType::class, [
                'label' => 'Платежна система',
                'choices' => [
                    'Stripe' => 'stripe',
                    'PayPal' => 'paypal',
                ],
                'expanded' => true,
                'required' => true,
            ])
            ->add('payment_test_mode', CheckboxType::class, [
                'label' => 'Тестов режим',
                'required' => false,
                'help' => 'Когато е активиран, плащанията ще се извършват в тестова среда.',
            ])
            ->add('payment_currency', ChoiceType::class, [
                'label' => 'Валута',
                'choices' => [
                    'EUR (€)' => 'EUR',
                    'USD ($)' => 'USD',
                    'BGN (лв)' => 'BGN',
                ],
                'required' => true,
            ])
            ->add('vat_rate', NumberType::class, [
                'label' => 'ДДС ставка (%)',
                'required' => true,
                'html5' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 0.01,
                ],
            ])
            ->add('stripe_public_key', TextType::class, [
                'label' => 'Stripe публичен ключ',
                'required' => false,
                'attr' => [
                    'class' => 'stripe-field',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('stripe_secret_key', PasswordType::class, [
                'label' => 'Stripe таен ключ',
                'required' => false,
                'attr' => [
                    'class' => 'stripe-field',
                    'autocomplete' => 'off',
                ],
                'always_empty' => false,
            ])
            ->add('stripe_webhook_secret', PasswordType::class, [
                'label' => 'Stripe webhook таен ключ',
                'required' => false,
                'attr' => [
                    'class' => 'stripe-field',
                    'autocomplete' => 'off',
                ],
                'always_empty' => false,
            ])
            ->add('paypal_client_id', TextType::class, [
                'label' => 'PayPal Client ID',
                'required' => false,
                'attr' => [
                    'class' => 'paypal-field',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('paypal_client_secret', PasswordType::class, [
                'label' => 'PayPal Client Secret',
                'required' => false,
                'attr' => [
                    'class' => 'paypal-field',
                    'autocomplete' => 'off',
                ],
                'always_empty' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'payment_settings',
        ]);
    }
} 