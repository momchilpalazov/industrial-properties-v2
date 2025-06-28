<?php

namespace App\Form;

use App\Entity\PropertySubmission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PropertySubmissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Property Basic Information
            ->add('propertyName', TextType::class, [
                'label' => 'Име на имота',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Въведете името на имота'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Името на имота е задължително']),
                    new Assert\Length(['min' => 3, 'max' => 255])
                ]
            ])
            ->add('propertyType', ChoiceType::class, [
                'label' => 'Тип имот',
                'choices' => [
                    'Производствена сграда' => 'manufacturing_building',
                    'Складова база' => 'warehouse',
                    'Логистичен център' => 'logistics_center',
                    'Търговски център' => 'commercial_center',
                    'Офис сграда' => 'office_building',
                    'Индустриален парк' => 'industrial_park',
                    'Производствен цех' => 'production_facility',
                    'Технологичен парк' => 'technology_park',
                    'Свободна зона' => 'free_zone',
                    'Земя за развитие' => 'development_land',
                    'Друго' => 'other'
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Моля изберете тип имот'])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание на имота',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Детайлно описание на имота, съоръжения, състояние, специфични характеристики...'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Описанието е задължително']),
                    new Assert\Length(['min' => 50, 'max' => 2000])
                ]
            ])
            
            // Location Information
            ->add('address', TextType::class, [
                'label' => 'Адрес',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Улица, номер, квартал'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Адресът е задължителен'])
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Град',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Въведете града'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Градът е задължителен'])
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
            ->add('postalCode', TextType::class, [
                'label' => 'Пощенски код',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Пощенски код'
                ]
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Географска ширина',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'step' => 'any',
                    'placeholder' => 'Например: 42.6977'
                ]
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Географска дължина', 
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'step' => 'any',
                    'placeholder' => 'Например: 23.3219'
                ]
            ])
            
            // Technical Specifications
            ->add('totalArea', NumberType::class, [
                'label' => 'Обща площ (кв.м)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'placeholder' => 'Обща площ в квадратни метри'
                ]
            ])
            ->add('usableArea', NumberType::class, [
                'label' => 'Полезна площ (кв.м)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'placeholder' => 'Полезна площ в квадратни метри'
                ]
            ])
            ->add('yearBuilt', NumberType::class, [
                'label' => 'Година на строеж',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1800,
                    'max' => date('Y') + 5,
                    'placeholder' => 'YYYY'
                ]
            ])
            ->add('constructionType', ChoiceType::class, [
                'label' => 'Тип конструкция',
                'required' => false,
                'choices' => [
                    'Стоманобетон' => 'reinforced_concrete',
                    'Стоманена конструкция' => 'steel_frame',
                    'Тухлена конструкция' => 'brick',
                    'Сглобяема конструкция' => 'prefab',
                    'Смесена конструкция' => 'mixed',
                    'Друго' => 'other'
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'placeholder' => 'Изберете тип конструкция'
            ])
            
            // Financial Information
            ->add('price', NumberType::class, [
                'label' => 'Цена (EUR)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => 'any',
                    'placeholder' => 'Цена в евро'
                ]
            ])
            ->add('priceType', ChoiceType::class, [
                'label' => 'Тип цена',
                'required' => false,
                'choices' => [
                    'Продажба' => 'sale',
                    'Наем/месечно' => 'rent_monthly',
                    'Наем/годишно' => 'rent_yearly',
                    'По запитване' => 'on_request'
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'placeholder' => 'Изберете тип цена'
            ])
            
            // Utilities & Infrastructure
            ->add('powerSupply', TextType::class, [
                'label' => 'Електрозахранване',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Например: 400V, 50Hz, 100kW'
                ]
            ])
            ->add('waterSupply', ChoiceType::class, [
                'label' => 'Водоснабдяване',
                'required' => false,
                'choices' => [
                    'Централно' => 'central',
                    'Собствен кладенец' => 'private_well',
                    'Смесено' => 'mixed',
                    'Няма' => 'none'
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'placeholder' => 'Изберете тип водоснабдяване'
            ])
            ->add('sewageSystem', ChoiceType::class, [
                'label' => 'Канализация',
                'required' => false,
                'choices' => [
                    'Централна' => 'central',
                    'Локално пречистване' => 'local_treatment',
                    'Септична яма' => 'septic_tank',
                    'Няма' => 'none'
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'placeholder' => 'Изберете тип канализация'
            ])
            ->add('gasSupply', ChoiceType::class, [
                'label' => 'Газоснабдяване',
                'required' => false,
                'choices' => [
                    'Централно' => 'central',
                    'Собствен резервоар' => 'private_tank',
                    'Няма' => 'none'
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'placeholder' => 'Изберете тип газоснабдяване'
            ])
            
            // Contact Information
            ->add('contactName', TextType::class, [
                'label' => 'Име за контакт',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Име на контактното лице'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Името за контакт е задължително'])
                ]
            ])
            ->add('contactEmail', EmailType::class, [
                'label' => 'Имейл за контакт',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'contact@example.com'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Имейлът за контакт е задължителен']),
                    new Assert\Email(['message' => 'Моля въведете валиден имейл адрес'])
                ]
            ])
            ->add('contactPhone', TelType::class, [
                'label' => 'Телефон за контакт',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+359 88 123 4567'
                ]
            ])
            ->add('contactCompany', TextType::class, [
                'label' => 'Компания',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Име на компанията (ако е приложимо)'
                ]
            ])
            
            // Files
            ->add('images', FileType::class, [
                'label' => 'Снимки на имота',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*',
                    'multiple' => true
                ],
                'constraints' => [
                    new Assert\All([
                        new Assert\File([
                            'maxSize' => '5M',
                            'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                            'mimeTypesMessage' => 'Моля качете валиден формат изображение (JPEG, PNG, GIF)'
                        ])
                    ])
                ]
            ])
            ->add('documents', FileType::class, [
                'label' => 'Документи',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => '.pdf,.doc,.docx,.xls,.xlsx',
                    'multiple' => true
                ],
                'constraints' => [
                    new Assert\All([
                        new Assert\File([
                            'maxSize' => '10M',
                            'mimeTypes' => [
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                            ],
                            'mimeTypesMessage' => 'Моля качете валиден документ (PDF, Word, Excel)'
                        ])
                    ])
                ]
            ])
            
            // Additional Information
            ->add('additionalInfo', TextareaType::class, [
                'label' => 'Допълнителна информация',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Всякаква друга важна информация за имота...'
                ]
            ])
            
            // Verification & Agreement
            ->add('dataAccuracy', CheckboxType::class, [
                'label' => 'Потвърждавам, че предоставената информация е точна и актуална',
                'mapped' => false,
                'constraints' => [
                    new Assert\IsTrue(['message' => 'Трябва да потвърдите точността на данните'])
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('shareAgreement', CheckboxType::class, [
                'label' => 'Съгласявам се информацията да бъде споделена в европейската мрежа',
                'mapped' => false,
                'constraints' => [
                    new Assert\IsTrue(['message' => 'Трябва да се съгласите с условията за споделяне'])
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PropertySubmission::class,
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
            'Финландия' => 'FI',
            'Дания' => 'DK',
            'Исландия' => 'IS',
            
            // Eastern Europe
            'България' => 'BG',
            'Румъния' => 'RO',
            'Полша' => 'PL',
            'Унгария' => 'HU',
            'Чехия' => 'CZ',
            'Словакия' => 'SK',
            'Словения' => 'SI',
            'Хърватия' => 'HR',
            'Сърбия' => 'RS',
            'Босна и Херцеговина' => 'BA',
            'Черна гора' => 'ME',
            'Албания' => 'AL',
            'Северна Македония' => 'MK',
            'Молдова' => 'MD',
            'Украйна' => 'UA',
            'Беларус' => 'BY',
            'Литва' => 'LT',
            'Латвия' => 'LV',
            'Естония' => 'EE',
            
            // Southern Europe
            'Гърция' => 'GR',
            'Кипър' => 'CY',
            'Малта' => 'MT',
            'Сан Марино' => 'SM',
            'Ватикан' => 'VA',
            'Андора' => 'AD'
        ];
    }
}
