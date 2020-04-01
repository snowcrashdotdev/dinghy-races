<?php

namespace App\Form;

use App\Entity\Score;
use App\Service\ImageUploader;
use App\Service\ReplayUploader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

class ScoreType extends AbstractType
{
    public function __construct(string $screenshot_dir, string $replay_dir)
    {
        $this->screenshot_uploader = new ImageUploader($screenshot_dir, $replay_dir);
        $this->replay_uploader = new ReplayUploader($replay_dir);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('points', IntegerType::class, [
                'label' => 'Score',
                'attr' => [
                    'placeholder' => 'Enter your highscore',
                ],
            ])
            ->add('videoUrl', UrlType::class, [
                'required' => false,
                'label' => 'Video URL',
                'attr' => [
                    'placeholder' => 'Example: https://www.twitch.tv/videos/423907124',
                    'type' => 'url'
                ]
            ])
            ->add('screenshot_file', FileType::class, [
                'required' => false,
                'label' => 'Screenshot',
                'constraints' => [
                    new Image([
                        'maxSize' => '6M'
                    ])
                ]
            ])
            ->add('replay_file', FileType::class, [
                'required' => false,
                'label' => 'Replay',
                'constraints' => [
                    new File([
                        'maxSize' => '4M',
                        'mimeTypes' => 'application/zip',
                        'mimeTypesMessage' => 'ZIP and upload your INP'
                    ])
                ]
            ])
            ->add('comment', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Add comment (number of credits, stages, etc.)',
                ],
                'label' => 'Comment'
            ])
            ->add('screenshot_file_remove', HiddenType::class, [
                'mapped' => false
            ])
            ->add('replay_file_remove', HiddenType::class, [
                'mapped' => false
            ])
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                [$this, 'onScoreSubmit']
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Score::class,
        ]);
    }

    public function onScoreSubmit(FormEvent $event)
    {
        $score = $event->getData();
        if ($event->getForm()->isValid()) {
            if ($screenshot_file = $score->getScreenshotFile()) {
                $screenshot_filename = $this->getScreenshotUploader()
                    ->upload($screenshot_file)
                ;

                if ($screenshot_filename) {
                    $score->setScreenshot($screenshot_filename);
                } else {
                    $event->getForm()->addError(new FormError('Unable to accept uploaded image.'));
                }
            }

            if ($replay_file = $score->getReplayFile()) {
                $replay_filename = $this->getReplayUploader()
                    ->upload($replay_file)
                ;

                if ($replay_filename) {
                    $score->setReplay($replay_filename);
                } else {
                    $event->getForm()->addError(new FormError('Unable to accept uploaded INP.'));
                }
            }
        } else {
            $score->setScreenshotFile(null);
            $score->setReplayFile(null);
        }
    }

    private function getScreenshotUploader(): ImageUploader
    {
        return $this->screenshot_uploader;
    }

    private function getReplayUploader(): ReplayUploader
    {
        return $this->replay_uploader;
    }
}
