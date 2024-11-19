<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HoneypotType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'invalid_message' => 'This form submission is spam.',
            'mapped' => false,
            'empty_data' => null,
            'attr' => [
                'tabindex' => '-1',
                'autocomplete' => 'off',
                'class' => 'field--honeypot',
                'value' => 'NewUser'
            ],
            'constraints' => [ new Blank([
                'message' => ''
            ]) ]
        ]);
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}