<?php

namespace App\Form;

use App\Entity\Tournament;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class TournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Title',
                'attr'=> [
                    'placeholder'=> 'Title'
                ]
            ])
            ->add('format', ChoiceType::class, [
                'required' => true,
                'choices' => Tournament::FORMATS,
                'choice_label' => function(?string $choice) {
                    return ucfirst(strtolower($choice));
                }
            ])
            ->add('start_date', DateType::class, [
                'required' => true,
                'label' => 'Start Date',
                'widget' => 'single_text',
            ])
            ->add('end_date', DateType::class, [
                'required' => true,
                'label' => 'End Date',
                'widget' => 'single_text'
            ])
            ->add('description', CKEditorType::class, [
                'label' => false,
                'required' => true,
                'attr'=> ['placeholder'=>'Describe this tournament.']
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
