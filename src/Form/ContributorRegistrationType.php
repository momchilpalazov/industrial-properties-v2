<?php

namespace App\Form;

use App\Entity\ContributorProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContributorRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Пълно име',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Въведете вашето пълно име'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Пълното име е задължително']),
                    new Assert\Length(['min' => 2, 'max' => 100])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Имейл адрес',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'your.email@example.com'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Имейлът е задължителен']),
                    new Assert\Email(['message' => 'Моля въведете валиден имейл адрес'])
                ]
            ])
            ->add('phone', TelType::class, [
                'label' => 'Телефон',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+359 88 123 4567'
                ]
            ])
            ->add('country', CountryType::class, [
                'label' => 'Държава',
                'attr' => [
                    'class' => 'form-select'
                ],
                'choice_loader' => null,
                'choices' => $this->getEuropeanCountries(),
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Моля изберете държава'])
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Град',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Въведете вашия град'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Градът е задължителен'])
                ]
            ])
            ->add('company', TextType::class, [
                'label' => 'Компания/Организация',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Име на компанията (по избор)'
                ]
            ])
            ->add('professionalBackground', ChoiceType::class, [
                'label' => 'Професионален фон',
                'choices' => [
                    'Брокер на недвижими имоти' => 'real_estate_broker',
                    'Строителен инженер' => 'construction_engineer',
                    'Архитект' => 'architect',
                    'Инвеститор' => 'investor',
                    'Строителен предприемач' => 'contractor',
                    'Консултант по недвижими имоти' => 'real_estate_consultant',
                    'Управител на имоти' => 'property_manager',
                    'Оценител на имоти' => 'property_appraiser',
                    'Банкиране и финансиране' => 'banking_finance',
                    'Индустриален експерт' => 'industrial_expert',
                    'Логистика и складиране' => 'logistics_warehousing',
                    'Друго' => 'other'
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Моля изберете вашия професионален фон'])
                ]
            ])
            ->add('experience', ChoiceType::class, [
                'label' => 'Опит в сферата',
                'choices' => [
                    'По-малко от 1 година' => 'less_than_1_year',
                    '1-3 години' => '1_3_years',
                    '3-5 години' => '3_5_years',
                    '5-10 години' => '5_10_years',
                    'Над 10 години' => 'over_10_years'
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('motivation', TextareaType::class, [
                'label' => 'Защо искате да се присъедините?',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Разкажете ни защо искате да станете част от европейската мрежа за индустриални имоти...'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Моля споделете вашата мотивация']),
                    new Assert\Length(['min' => 50, 'max' => 1000])
                ]
            ])
            ->add('languagesSpoken', ChoiceType::class, [
                'label' => 'Езици които говорите',
                'choices' => [
                    'Български' => 'bg',
                    'English' => 'en',
                    'Deutsch' => 'de',
                    'Русский' => 'ru',
                    'Français' => 'fr',
                    'Español' => 'es',
                    'Italiano' => 'it',
                    'Polski' => 'pl',
                    'Nederlands' => 'nl',
                    'Čeština' => 'cs',
                    'Hrvatski' => 'hr',
                    'Română' => 'ro',
                    'Magyar' => 'hu',
                    'Slovenčina' => 'sk',
                    'Slovenščina' => 'sl'
                ],
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'language-checkboxes'
                ]
            ])
            ->add('agreeToTerms', CheckboxType::class, [
                'label' => 'Съгласявам се с условията за ползване и политиката за поверителност',
                'mapped' => false,
                'constraints' => [
                    new Assert\IsTrue(['message' => 'Трябва да се съгласите с условията за ползване'])
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('subscribeNewsletter', CheckboxType::class, [
                'label' => 'Искам да получавам новини и обновления (препоръчително)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContributorProfile::class,
        ]);
    }

    /**
     * Get all European countries for the dropdown
     */
    private function getEuropeanCountries(): array
    {
        return [
            // Western Europe
            'Германия' => 'DE',
            'Франция' => 'FR', 
            'Великобритания' => 'GB',
            'Италия' => 'IT',
            'Испания' => 'ES',
            'Нидерландия' => 'NL',
            'Белгия' => 'BE',
            'Австрия' => 'AT',
            'Швейцария' => 'CH',
            'Ирландия' => 'IE',
            'Португалия' => 'PT',
            'Люксембург' => 'LU',
            'Монако' => 'MC',
            
            // Northern Europe  
            'Швеция' => 'SE',
            'Норвегия' => 'NO',
            'Дания' => 'DK',
            'Финландия' => 'FI',
            'Исландия' => 'IS',
            
            // Central Europe
            'Полша' => 'PL',
            'Чехия' => 'CZ',
            'Унгария' => 'HU',
            'Словакия' => 'SK',
            'Словения' => 'SI',
            
            // Eastern Europe
            'България' => 'BG',
            'Румъния' => 'RO',
            'Хърватия' => 'HR',
            'Сърбия' => 'RS',
            'Босна и Херцеговина' => 'BA',
            'Албания' => 'AL',
            'Северна Македония' => 'MK',
            'Черна гора' => 'ME',
            'Молдова' => 'MD',
            'Украйна' => 'UA',
            'Беларус' => 'BY',
            
            // Baltic States
            'Естония' => 'EE',
            'Латвия' => 'LV',
            'Литва' => 'LT',
            
            // Other
            'Русия' => 'RU',
            'Турция' => 'TR',
            'Кипър' => 'CY',
            'Малта' => 'MT',
            'Андора' => 'AD',
            'Лихтенщайн' => 'LI',
            'Сан Марино' => 'SM',
            'Ватикан' => 'VA'
        ];
    }
}
