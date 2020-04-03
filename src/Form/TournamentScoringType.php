<?php

namespace App\Form;

use App\Entity\TournamentScoring;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TournamentScoringType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cutoff_line', IntegerType::class, [
                'label' => 'Cutoff #',
                'attr' => [
                    'placeholder' => 'Number of scores to drop from each team.'
                ]
            ])
            ->add('cutoff_score', IntegerType::class, [
                'label' => 'Cutoff Score',
                'attr' => [
                    'placeholder' => 'Points to assign scores below cutoff.'
                ]
            ])
            ->add('deadline', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Deadline'
            ])
            ->add('noshow_score', IntegerType::class, [
                'label' => 'No-Show Score',
                'attr' => [
                    'placeholder' => 'Points to assign to no-shows above cutoff.',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TournamentScoring::class,
        ]);
    }
}
