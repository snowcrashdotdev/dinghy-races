<?php

namespace App\Form;

use App\Entity\TournamentScoring;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TournamentScoringType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('points_table')
            ->add('cutoff_date')
            ->add('cutoff_line')
            ->add('cutoff_score')
            ->add('noshow_score')
            ->add('tournament')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TournamentScoring::class,
        ]);
    }
}
