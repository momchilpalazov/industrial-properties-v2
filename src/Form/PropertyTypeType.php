<?php

namespace App\Form;

use App\Entity\PropertyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class PropertyTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Име (BG)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Въведете име на български'
                ]
            ])
            ->add('nameEn', TextType::class, [
                'label' => 'Име (EN)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter name in English'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание (BG)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Въведете описание на български'
                ]
            ])
            ->add('descriptionEn', TextareaType::class, [
                'label' => 'Описание (EN)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Enter description in English'
                ]
            ])
            ->add('parent', EntityType::class, [
                'class' => PropertyType::class,
                'label' => 'Родителска категория',
                'required' => false,
                'placeholder' => '-- Няма родителска категория --',
                'attr' => [
                    'class' => 'form-select'
                ],
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('pt')
                        ->orderBy('pt.name', 'ASC');
                    
                    // Ако редактираме съществуващ тип, изключваме го и неговите деца от списъка
                    if (isset($options['data']) && $options['data']->getId()) {
                        $qb->where('pt != :current')
                           ->setParameter('current', $options['data']);
                           
                        // Ако има деца, изключваме ги също
                        if (!$options['data']->getChildren()->isEmpty()) {
                            $children = $options['data']->getChildren()->toArray();
                            $qb->andWhere('pt NOT IN (:children)')
                               ->setParameter('children', $children);
                        }
                    }
                    
                    return $qb;
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PropertyType::class,
        ]);
    }
} 