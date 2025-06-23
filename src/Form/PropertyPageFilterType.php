<?php

namespace App\Form;

use App\Entity\PropertyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\Property;
use Doctrine\ORM\EntityRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class PropertyPageFilterType extends PropertyFilterType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->translator->getLocale();
        
        $builder
            ->add('status', ChoiceType::class, [
                'label' => $this->translator->trans('property.form.status_label', [], 'property'),
                'choices' => [
                    $this->translator->trans('property.form.status_choices.available', [], 'property') => Property::STATUS_AVAILABLE,
                    $this->translator->trans('property.form.status_choices.sold', [], 'property') => Property::STATUS_SOLD,
                    $this->translator->trans('property.form.status_choices.reserved', [], 'property') => Property::STATUS_RESERVED,
                    $this->translator->trans('property.form.status_choices.rented', [], 'property') => Property::STATUS_RENTED,
                    $this->translator->trans('property.form.status_choices.pending', [], 'property') => Property::STATUS_PENDING,
                    $this->translator->trans('property.form.status_choices.auction', [], 'property') => Property::STATUS_AUCTION
                ],
                'placeholder' => $this->translator->trans('property.form.status_choices.all', [], 'property'),
                'required' => false,
                'attr' => ['class' => 'form-select']
            ])
            ->add('type', EntityType::class, [
                'class' => PropertyType::class,
                'choice_label' => function (PropertyType $propertyType) use ($locale) {
                    $prefix = $propertyType->getParent() ? '— ' : '';
                    return $prefix . $propertyType->getLocalizedName($locale);
                },
                'required' => false,
                'placeholder' => $this->translator->trans('property.form.type_placeholder', [], 'property'),
                'label' => $this->translator->trans('property.form.type_label', [], 'property'),
                'group_by' => function (PropertyType $propertyType) use ($locale) {
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
            ])
            ->add('min_price', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.min_price_label', [], 'property'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.min_price_placeholder', [], 'property')
                ]
            ])
            ->add('max_price', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.max_price_label', [], 'property'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.max_price_placeholder', [], 'property')
                ]
            ])
            ->add('min_area', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.min_area_label', [], 'property'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.min_area_placeholder', [], 'property')
                ]
            ])
            ->add('max_area', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.max_area_label', [], 'property'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.max_area_placeholder', [], 'property')
                ]
            ])
            ->add('sort', ChoiceType::class, [
                'label' => $this->translator->trans('property.form.sort_label', [], 'property'),
                'choices' => [
                    $this->translator->trans('property.form.sort_choices.newest', [], 'property') => 'p.createdAt',
                    $this->translator->trans('property.form.sort_choices.price_asc', [], 'property') => 'p.price',
                    $this->translator->trans('property.form.sort_choices.price_desc', [], 'property') => 'p.price',
                    $this->translator->trans('property.form.sort_choices.area_asc', [], 'property') => 'p.area',
                    $this->translator->trans('property.form.sort_choices.area_desc', [], 'property') => 'p.area'
                ],
                'placeholder' => $this->translator->trans('property.form.sort_placeholder', [], 'property'),
                'required' => false,
                'attr' => ['class' => 'form-select']
            ])
            ->add('direction', ChoiceType::class, [
                'label' => $this->translator->trans('property.form.direction_label', [], 'property'),
                'choices' => [
                    $this->translator->trans('property.form.direction_choices.asc', [], 'property') => 'asc',
                    $this->translator->trans('property.form.direction_choices.desc', [], 'property') => 'desc'
                ],
                'data' => 'desc',
                'required' => false,
                'attr' => ['class' => 'form-select']
            ]);
    }
} 