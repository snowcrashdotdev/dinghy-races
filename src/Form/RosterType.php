<?php

namespace App\Form;

use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use App\Form\Type\UserSelectorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class RosterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('members', CollectionType::class, [
                'required' => false,
                'label' => false,
                'entry_type' => UserSelectorType::class,
                'entry_options' => [
                    'attr' => [
                        'tournament' => $builder->getData()->getTournament()
                    ]
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false
            ])
            ->setMethod('PATCH')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
            'attr' => [
                'class' => 'ajax-form'
            ]
        ]);
    }
}
