<?php

namespace App\Form;

use App\Entity\PersonalBest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonalBestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('created_at')
            ->add('updated_at')
            ->add('video_url')
            ->add('screenshot')
            ->add('comment')
            ->add('points')
            ->add('points_history')
            ->add('user')
            ->add('game')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PersonalBest::class,
        ]);
    }
}
