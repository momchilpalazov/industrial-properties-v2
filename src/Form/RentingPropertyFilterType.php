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

class RentingPropertyFilterType extends PropertyFilterType
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
                'label' => $this->translator->trans('property.form.status_label', [], 'renting'),
                'choices' => [
                    $this->translator->trans('property.form.status_choices.available', [], 'renting') => Property::STATUS_AVAILABLE,
                    $this->translator->trans('property.form.status_choices.sold', [], 'renting') => Property::STATUS_SOLD,
                    $this->translator->trans('property.form.status_choices.reserved', [], 'renting') => Property::STATUS_RESERVED,
                    $this->translator->trans('property.form.status_choices.rented', [], 'renting') => Property::STATUS_RENTED,
                    $this->translator->trans('property.form.status_choices.pending', [], 'renting') => Property::STATUS_PENDING,
                    $this->translator->trans('property.form.status_choices.auction', [], 'renting') => Property::STATUS_AUCTION
                ],
                'placeholder' => $this->translator->trans('property.form.status_choices.all', [], 'renting'),
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
                'placeholder' => $this->translator->trans('property.form.type_placeholder', [], 'renting'),
                'label' => $this->translator->trans('property.form.type_label', [], 'renting'),
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
                'label' => $this->translator->trans('property.form.min_price_label', [], 'renting'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.min_price_placeholder', [], 'renting')
                ]
            ])
            ->add('max_price', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.max_price_label', [], 'renting'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.max_price_placeholder', [], 'renting')
                ]
            ])
            ->add('min_area', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.min_area_label', [], 'renting'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.min_area_placeholder', [], 'renting')
                ]
            ])
            ->add('max_area', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.max_area_label', [], 'renting'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.max_area_placeholder', [], 'renting')
                ]
            ])
            ->add('sort', ChoiceType::class, [
                'label' => $this->translator->trans('property.form.sort_label', [], 'renting'),
                'choices' => [
                    $this->translator->trans('property.form.sort_choices.newest', [], 'renting') => 'p.createdAt',
                    $this->translator->trans('property.form.sort_choices.price_asc', [], 'renting') => 'p.price',
                    $this->translator->trans('property.form.sort_choices.price_desc', [], 'renting') => 'p.price',
                    $this->translator->trans('property.form.sort_choices.area_asc', [], 'renting') => 'p.area',
                    $this->translator->trans('property.form.sort_choices.area_desc', [], 'renting') => 'p.area'
                ],
                'placeholder' => $this->translator->trans('property.form.sort_placeholder', [], 'renting'),
                'required' => false,
                'attr' => ['class' => 'form-select']
            ])
            ->add('direction', ChoiceType::class, [
                'label' => $this->translator->trans('property.form.direction_label', [], 'renting'),
                'choices' => [
                    $this->translator->trans('property.form.direction_choices.asc', [], 'renting') => 'asc',
                    $this->translator->trans('property.form.direction_choices.desc', [], 'renting') => 'desc'
                ],
                'data' => 'desc',
                'required' => false,
                'attr' => ['class' => 'form-select']
            ]);
    }
} 