<?php

namespace App\Form;

use App\Entity\Score;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ScoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('points', IntegerType::class, [
                'attr' => [
                    'placeholder' => 'Enter your highscore',
                    'class' => 'score-input'
            ],
                'label' => 'Score',
                'label_attr' => ['class'=>'score-label']
            ])
            ->add('videoUrl', UrlType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Example: https://www.twitch.tv/videos/423907124',
                    'class' => 'score-input'
                ],
                'label' => 'Video URL',
                'label_attr' => ['class'=>'score-label']
            ])
            ->add('screenshot_file', FileType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Any image file will do',
                    'class' => 'score-input'
                ],
                'label' => 'Screenshot',
                'label_attr' => ['class'=>'score-label']
            ])
            ->add('comment', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Add comment (number of credits, stages, etc.)',
                    'class' => 'score-input'
                ],
                'label' => 'Comment',
                'label_attr' => ['class'=>'score-label']
            ])
            ->add('screenshot_file_remove', HiddenType::class, [
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Score::class,
        ]);
    }
}
