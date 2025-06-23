<?php

namespace App\Form;

use App\Entity\PropertyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Property;
use Doctrine\ORM\EntityRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class PropertyFilterType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->translator->getLocale();
        
        $builder
            ->add('status', ChoiceType::class, [
                'label' => $this->translator->trans('property.form.status_label'),
                'choices' => [
                    $this->translator->trans('property.form.status_choices.available') => Property::STATUS_AVAILABLE,
                    $this->translator->trans('property.form.status_choices.sold') => Property::STATUS_SOLD,
                    $this->translator->trans('property.form.status_choices.reserved') => Property::STATUS_RESERVED,
                    $this->translator->trans('property.form.status_choices.rented') => Property::STATUS_RENTED,
                    $this->translator->trans('property.form.status_choices.pending') => Property::STATUS_PENDING,
                    $this->translator->trans('property.form.status_choices.auction') => Property::STATUS_AUCTION
                ],
                'placeholder' => $this->translator->trans('property.form.status_choices.all'),
                'required' => false,
                'attr' => ['class' => 'form-select']
            ])            ->add('type', EntityType::class, [
                'class' => PropertyType::class,
                'choice_label' => function (PropertyType $propertyType) use ($locale) {
                    $prefix = $propertyType->getParent() ? '— ' : '';
                    return $prefix . $propertyType->getLocalizedName($locale);
                },
                'required' => false,
                'placeholder' => $this->translator->trans('property.form.type_placeholder'),
                'label' => $this->translator->trans('property.form.type_label'),                'group_by' => function (PropertyType $propertyType) use ($locale) {
                    if ($propertyType->getParent()) {
                        return $propertyType->getParent()->getLocalizedName($locale);
                    }
                    return null;
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('pt')
                        ->orderBy('CASE WHEN pt.parent IS NULL THEN 0 ELSE 1 END', 'ASC')
                        ->addOrderBy('pt.name', 'ASC');
                },
                'attr' => [
                    'class' => 'form-select property-type-filter'
                ]
            ])            ->add('min_price', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.min_price_label'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.min_price_placeholder')
                ]
            ])
            ->add('max_price', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.max_price_label'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.max_price_placeholder')
                ]
            ])
            ->add('min_area', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.min_area_label'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.min_area_placeholder')
                ]
            ])
            ->add('max_area', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.max_area_label'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.max_area_placeholder')
                ]
            ])            ->add('sort', ChoiceType::class, [
                'label' => $this->translator->trans('property.form.sort_label'),
                'choices' => [
                    $this->translator->trans('property.form.sort_choices.newest') => 'p.createdAt',
                    $this->translator->trans('property.form.sort_choices.price_asc') => 'p.price',
                    $this->translator->trans('property.form.sort_choices.price_desc') => 'p.price',
                    $this->translator->trans('property.form.sort_choices.area_asc') => 'p.area',
                    $this->translator->trans('property.form.sort_choices.area_desc') => 'p.area'
                ],
                'placeholder' => $this->translator->trans('property.form.sort_placeholder'),
                'required' => false,
                'attr' => ['class' => 'form-select']
            ])
            ->add('direction', ChoiceType::class, [
                'label' => $this->translator->trans('property.form.direction_label'),
                'choices' => [
                    $this->translator->trans('property.form.direction_choices.asc') => 'asc',
                    $this->translator->trans('property.form.direction_choices.desc') => 'desc'
                ],
                'data' => 'desc',
                'required' => false,
                'attr' => ['class' => 'form-select']
            ]);
    }    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'attr' => [
                'class' => 'property-filter-form'
            ]
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
} 