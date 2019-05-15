<?php
namespace App\Form\Type;

use App\Form\DataTransformer\NameToGameTransformer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameSelectorType extends AbstractType
{
    private $transformer;

    public function __construct(NameToGameTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'invalid_message' => 'The selected game does not exist',
            'attr'=> ['placeholder' => 'Game', 'class' => 'game-search']
        ]);
    }

    public function getParent()
    {
        return TextType::class;
    }
}