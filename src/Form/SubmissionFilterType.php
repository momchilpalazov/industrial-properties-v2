<?php

namespace App\Form;

use App\Entity\PropertySubmission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubmissionFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'Статус',
                'choices' => [
                    'Всички' => '',
                    'Чакащо' => PropertySubmission::STATUS_PENDING,
                    'Одобрено' => PropertySubmission::STATUS_APPROVED,
                    'Отхвърлено' => PropertySubmission::STATUS_REJECTED,
                ],
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('propertyType', ChoiceType::class, [
                'label' => 'Тип имот',
                'choices' => [
                    'Всички типове' => '',
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
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('country', CountryType::class, [
                'label' => 'Държава',
                'required' => false,
                'choice_loader' => null,
                'choices' => $this->getEuropeanCountries(),
                'placeholder' => 'Всички държави',
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Град',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Търсене по град'
                ]
            ])
            ->add('contributorName', TextType::class, [
                'label' => 'Contributor',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Име на contributor'
                ]
            ])
            ->add('dateFrom', DateType::class, [
                'label' => 'От дата',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dateTo', DateType::class, [
                'label' => 'До дата',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('sortBy', ChoiceType::class, [
                'label' => 'Подреждане по',
                'choices' => [
                    'Дата (най-нови)' => 'submittedAt_desc',
                    'Дата (най-стари)' => 'submittedAt_asc',
                    'Статус' => 'status',
                    'Град' => 'city',
                    'Държава' => 'country',
                    'Contributor' => 'contributor_name'
                ],
                'required' => false,
                'data' => 'submittedAt_desc',
                'attr' => [
                    'class' => 'form-select'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'attr' => [
                'class' => 'submission-filter-form'
            ]
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
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
