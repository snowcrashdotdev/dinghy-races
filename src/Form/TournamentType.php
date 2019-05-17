<?php

namespace App\Form;

use App\Entity\Tournament;
use App\Form\TeamType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\Type\GameSelectorType;

class TournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['attr'=>array('placeholder'=>'Title')])
            ->add('start_date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('end_date', DateType::class, [
                'widget' => 'single_text'
            ])
            ->add('description', CKEditorType::class, [
                'attr'=> ['placeholder'=>'Describe this tournament.']
            ])
            ->add('games', CollectionType::class, [
                'entry_type' => GameSelectorType::class,
                'entry_options' => [
                    'label' => false,
                    'attr' => ['class' => 'full-width margin-y']
                ],
                'allow_add' => true,
                'prototype' => true,
                'prototype_name' => '__game__',
                'required' => false,
                'label' => 'Games',
            ])
            ->add('teams', CollectionType::class, [
                'entry_type' => TeamType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'prototype' => true,
                'prototype_name' => '__team__',
                'by_reference' => false,
                'required' => false,
                'label' => 'Teams'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tournament::class,
        ]);
    }
}
