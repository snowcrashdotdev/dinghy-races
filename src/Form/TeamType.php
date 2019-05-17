<?php

namespace App\Form;

use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\Type\MemberSelectorType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'attr'=>[
                    'placeholder'=>'Team Name',
                    'class' => 'full-width'
                ]
            ])
            ->add('members', CollectionType::class, [
                'entry_type' => MemberSelectorType::class,
                'entry_options' => [
                    'label' => false
                ],
                'allow_add' => true,
                'prototype' => true,
                'prototype_name' => '__member__',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
