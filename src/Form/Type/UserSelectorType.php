<?php
namespace App\Form\Type;

use App\Form\DataTransformer\UserToUsernameTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class UserSelectorType extends AbstractType
{
    private $transformer;

    public function __construct(UserToUsernameTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tournament = $options['attr']['tournament'];
        $transformer = $this->transformer;
        $builder->addModelTransformer($this->transformer);

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) use ($transformer, $tournament) {
                $transformer->setTournament($tournament);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'invalid_message' => 'Unable to find user by that name',
        ]);
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}