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

class AuctionPropertyFilterType extends PropertyFilterType
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
                'label' => $this->translator->trans('property.form.status_label', [], 'auction'),
                'choices' => [
                    $this->translator->trans('property.form.status_choices.available', [], 'auction') => Property::STATUS_AVAILABLE,
                    $this->translator->trans('property.form.status_choices.sold', [], 'auction') => Property::STATUS_SOLD,
                    $this->translator->trans('property.form.status_choices.reserved', [], 'auction') => Property::STATUS_RESERVED,
                    $this->translator->trans('property.form.status_choices.rented', [], 'auction') => Property::STATUS_RENTED,
                    $this->translator->trans('property.form.status_choices.pending', [], 'auction') => Property::STATUS_PENDING,
                    $this->translator->trans('property.form.status_choices.auction', [], 'auction') => Property::STATUS_AUCTION
                ],
                'placeholder' => $this->translator->trans('property.form.status_choices.all', [], 'auction'),
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
                'placeholder' => $this->translator->trans('property.form.type_placeholder', [], 'auction'),
                'label' => $this->translator->trans('property.form.type_label', [], 'auction'),
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
                'label' => $this->translator->trans('property.form.min_price_label', [], 'auction'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.form.from_placeholder', [], 'auction')
                ]
            ])
            ->add('max_price', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.max_price_label', [], 'auction'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.form.to_placeholder', [], 'auction')
                ]
            ])
            ->add('min_area', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.min_area_label', [], 'auction'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.form.from_placeholder', [], 'auction')
                ]
            ])
            ->add('max_area', NumberType::class, [
                'required' => false,
                'label' => $this->translator->trans('property.form.max_area_label', [], 'auction'),
                'attr' => [
                    'placeholder' => $this->translator->trans('property.form.to_placeholder', [], 'auction')
                ]
            ])
            ->add('sort', ChoiceType::class, [
                'label' => $this->translator->trans('property.form.sort_label', [], 'auction'),
                'choices' => [
                    $this->translator->trans('property.form.sort_choices.newest', [], 'auction') => 'p.createdAt',
                    $this->translator->trans('property.form.sort_choices.price_asc', [], 'auction') => 'p.price',
                    $this->translator->trans('property.form.sort_choices.price_desc', [], 'auction') => 'p.price',
                    $this->translator->trans('property.form.sort_choices.area_asc', [], 'auction') => 'p.area',
                    $this->translator->trans('property.form.sort_choices.area_desc', [], 'auction') => 'p.area'
                ],
                'placeholder' => $this->translator->trans('property.form.sort_placeholder', [], 'auction'),
                'required' => false,
                'attr' => ['class' => 'form-select']
            ])
            ->add('direction', ChoiceType::class, [
                'label' => $this->translator->trans('property.form.direction_label', [], 'auction'),
                'choices' => [
                    $this->translator->trans('property.form.direction_choices.asc', [], 'auction') => 'asc',
                    $this->translator->trans('property.form.direction_choices.desc', [], 'auction') => 'desc'
                ],
                'data' => 'desc',
                'required' => false,
                'attr' => ['class' => 'form-select']
            ]);
    }
} 