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
            ->add('start_date', DateType::class, ['widget' => 'single_text', 'help'=>'Start Date'])
            ->add('end_date', DateType::class, ['widget' => 'single_text', 'help'=>'End Date'])
            ->add('description', CKEditorType::class, ['attr'=>array('placeholder'=>'Description')])
            ->add('games', CollectionType::class, [
                'entry_type' => GameSelectorType::class,
                'allow_add' => true,
                'prototype' => true,
                'prototype_name' => '__game__',
                'required' => false
                ])
            ->add('teams', CollectionType::class, [
                'entry_type' => TeamType::class,
                'allow_add' => true,
                'prototype' => true,
                'prototype_name' => '__team__',
                'by_reference' => false,
                'required' => false
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
