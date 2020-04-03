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
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class ScoreType extends AbstractType
{
    public function __construct(string $screenshot_dir, string $replay_dir)
    {
        $this->image_uploader = new ImageUploader($screenshot_dir);
        $this->replay_uploader = new ReplayUploader($replay_dir);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('points', IntegerType::class, [
                'required' => true,
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
                'mapped' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '6M'
                    ])
                ]
            ])
            ->add('replay_file', FileType::class, [
                'required' => false,
                'mapped' => false,
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
                'label' => 'Comment',
                'attr' => [
                    'placeholder' => 'Add comment (number of credits, stages, etc.)',
                ],
                'constraints' => [
                    new Length([
                        'max' => 140
                    ])
                ]
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

    public function onScoreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        if (!$form->isValid()) { return; }
        $score = $event->getData();
        $new_screenshot = $form->get('screenshot_file')->getData();
        $new_replay = $form->get('replay_file')->getData();
        $screenshot_remove = $form->get('screenshot_file_remove')->getData();
        $replay_remove = $form->get('replay_file_remove')->getData();

        if ($new_screenshot) {
            $filename = $this->getImageUploader()->upload($new_screenshot);

            if ($filename) {
                $score->setScreenshot($filename);
            } else {
                $score->setScreenshotFile(null);
                $form->addError(new FormError('Unable to accept uploaded image.'));
            }
        } else if ($screenshot_remove === '1') {
            $score->setScreenshot(null);
        }

        if ($new_replay) {
            $filename = $this->getReplayUploader()->upload($new_replay);

            if ($filename) {
                $score->setReplay($filename);
            } else {
                $score->setReplayFile(null);
                $event->getForm()->addError(new FormError('Unable to accept uploaded INP.'));
            }
        } else if ($replay_remove === '1') {
            $score->setReplay(null);
        }

        if (
            empty($score->getScreenshot()) &&
            empty($score->getReplay()) &&
            empty($score->getVideoUrl())
        ) {
            $form->addError(new FormError('A valid score requires a video URL, screenshot, or replay file.'));
        }
        $event->setData($score);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Score::class,
        ]);
    }

    private function getImageUploader(): ImageUploader
    {
        return $this->image_uploader;
    }

    private function getReplayUploader(): ReplayUploader
    {
        return $this->replay_uploader;
    }
}
