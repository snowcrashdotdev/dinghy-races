<?php

namespace App\Form;

use App\Entity\Tournament;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class TournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['attr'=>array('placeholder'=>'Title')])
            ->add('start_date', DateType::class, ['widget' => 'single_text', 'help'=>'Start Date'])
            ->add('end_date', DateType::class, ['widget' => 'single_text', 'help'=>'End Date'])
            ->add('description', CKEditorType::class, ['attr'=>array('placeholder'=>'Description')])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tournament::class,
        ]);
    }
}
