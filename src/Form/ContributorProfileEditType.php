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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContributorProfileEditType extends AbstractType
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
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Държавата е задължителна'])
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Град',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'София, Пловдив, Варна...'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Градът е задължителен'])
                ]
            ])
            ->add('linkedinProfile', TextType::class, [
                'label' => 'LinkedIn профил',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://linkedin.com/in/yourprofile'
                ]
            ])
            ->add('companyWebsite', TextType::class, [
                'label' => 'Уебсайт на компанията',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://yourcompany.com'
                ]
            ])
            ->add('yearsOfExperience', ChoiceType::class, [
                'label' => 'Години опит в недвижими имоти',
                'choices' => [
                    'Под 1 година' => '0-1',
                    '1-3 години' => '1-3',
                    '3-5 години' => '3-5',
                    '5-10 години' => '5-10',
                    'Над 10 години' => '10+'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('expertiseAreas', ChoiceType::class, [
                'label' => 'Области на експертиза',
                'choices' => [
                    'Индустриални имоти' => 'industrial',
                    'Складови помещения' => 'warehouse',
                    'Производствени площи' => 'manufacturing',
                    'Логистични центрове' => 'logistics',
                    'Търговски имоти' => 'commercial',
                    'Офиси' => 'office',
                    'Земеделски земи' => 'agricultural',
                    'Смесено използване' => 'mixed_use',
                    'Специализирани обекти' => 'specialized'
                ],
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-check'
                ]
            ])
            ->add('geographicCoverage', ChoiceType::class, [
                'label' => 'Географско покритие',
                'choices' => [
                    'България' => 'BG',
                    'Румъния' => 'RO',
                    'Сърбия' => 'RS',
                    'Хърватия' => 'HR',
                    'Словения' => 'SI',
                    'Унгария' => 'HU',
                    'Чехия' => 'CZ',
                    'Словакия' => 'SK',
                    'Полша' => 'PL',
                    'Северна Македония' => 'MK',
                    'Черна гора' => 'ME',
                    'Босна и Херцеговина' => 'BA'
                ],
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-check'
                ]
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Биография',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Разкажете за себе си, вашия опит и мотивацията...'
                ],
                'constraints' => [
                    new Assert\Length(['max' => 1000])
                ]
            ])
            ->add('profileImage', FileType::class, [
                'label' => 'Профилна снимка',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Моля качете валидна снимка (JPEG, PNG, WebP)'
                    ])
                ]
            ])
            ->add('preferredContactMethod', ChoiceType::class, [
                'label' => 'Предпочитан начин за контакт',
                'choices' => [
                    'Имейл' => 'email',
                    'Телефон' => 'phone',
                    'WhatsApp' => 'whatsapp',
                    'LinkedIn' => 'linkedin'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('marketingOptIn', CheckboxType::class, [
                'label' => 'Желая да получавам маркетингови съобщения',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('dataProcessingConsent', CheckboxType::class, [
                'label' => 'Съгласен съм с обработката на личните ми данни',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'constraints' => [
                    new Assert\IsTrue(['message' => 'Трябва да се съгласите с обработката на данните'])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContributorProfile::class,
        ]);
    }
}
