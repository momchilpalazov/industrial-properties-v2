<?php

namespace App\Form;

use App\Entity\ContributorReward;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RewardClaimType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rewardType', ChoiceType::class, [
                'label' => 'Тип награда',
                'choices' => [
                    'Качествено предложение (50 точки)' => 'quality_submission',
                    'Масово предложение (100 точки)' => 'bulk_submission', 
                    'Бонус за привличане (75 точки)' => 'referral_bonus',
                    'Специален принос (200 точки)' => 'special_contribution',
                    'Milestone бонус (150 точки)' => 'milestone_bonus',
                    'Месечна активност (30 точки)' => 'monthly_activity',
                    'Първо предложение (25 точки)' => 'first_submission',
                    'Високо качество (75 точки)' => 'high_quality'
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Моля изберете тип награда'])
                ]
            ])
            ->add('claimReason', TextareaType::class, [
                'label' => 'Обосновка за заявката',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Опишете подробно защо смятате, че заслужавате тази награда. Включете конкретни примери и постижения...'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Обосновката е задължителна']),
                    new Assert\Length([
                        'min' => 50,
                        'max' => 1000,
                        'minMessage' => 'Обосновката трябва да бъде поне {{ limit }} символа',
                        'maxMessage' => 'Обосновката не може да бъде повече от {{ limit }} символа'
                    ])
                ]
            ])
            ->add('evidenceDescription', TextareaType::class, [
                'label' => 'Доказателства',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Посочете конкретни примери, линкове, или други доказателства, които подкрепят вашата заявка...'
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 500,
                        'maxMessage' => 'Описанието на доказателствата не може да бъде повече от {{ limit }} символа'
                    ])
                ]
            ])
            ->add('expectedPoints', NumberType::class, [
                'label' => 'Очаквани точки',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true,
                    'placeholder' => 'Ще се попълни автоматично'
                ],
                'help' => 'Точките се определят автоматично въз основа на избрания тип награда'
            ])
            ->add('urgencyLevel', ChoiceType::class, [
                'label' => 'Приоритет',
                'choices' => [
                    'Нормален' => 'normal',
                    'Висок' => 'high',
                    'Спешен' => 'urgent'
                ],
                'data' => 'normal',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Изберете приоритет само ако наградата е наистина спешна'
            ])
            ->add('agreeToTerms', CheckboxType::class, [
                'label' => 'Потвърждавам, че предоставената информация е вярна и точна',
                'mapped' => false,
                'constraints' => [
                    new Assert\IsTrue(['message' => 'Трябва да потвърдите, че информацията е вярна'])
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('acceptEvaluation', CheckboxType::class, [
                'label' => 'Разбирам, че заявката ми ще бъде оценена от администраторите',
                'mapped' => false,
                'constraints' => [
                    new Assert\IsTrue(['message' => 'Трябва да приемете условията за оценка'])
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContributorReward::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }

    /**
     * Get points for reward type
     */
    public static function getRewardPoints(): array
    {
        return [
            'quality_submission' => 50,
            'bulk_submission' => 100,
            'referral_bonus' => 75,
            'special_contribution' => 200,
            'milestone_bonus' => 150,
            'monthly_activity' => 30,
            'first_submission' => 25,
            'high_quality' => 75
        ];
    }
}
