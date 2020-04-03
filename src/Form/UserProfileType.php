<?php

namespace App\Form;

use App\Entity\Profile;
use App\Service\ImageUploader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

class UserProfileType extends AbstractType
{
    public function __construct(string $pfp_dir)
    {
        $this->image_uploader = new ImageUploader($pfp_dir);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('social', UrlType::class, [
                'required' => false,
                'label' => 'Streaming/Social URL'
            ])
            ->add('picture_file', FileType::class, [
                'required' => false,
                'constraints' => [
                    new Image([
                        'allowLandscape' => false,
                        'allowPortrait' => false
                    ])
                ]
            ])
            ->add('picture_file_remove', HiddenType::class, [
                'mapped' => false
            ])
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                [$this, 'onSubmit']
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }

    public function onSubmit(FormEvent $event)
    {
        $profile = $event->getData();
        $picture_remove = $event->getForm()
            ->get('picture_file_remove')
            ->getData()
        ;
        if ($event->getForm()->isValid()) {
            if ($picture_file = $profile->getPictureFile()) {
                $picture_filename = $this->getImageUploader()
                    ->upload($picture_file)
                ;

                if ($picture_filename) {
                    $profile->setPicture($picture_filename);
                } else {
                    $profile->setPictureFile(null);
                    $event->getForm()->addError(new FormError('Unable to accept uploaded image.'));
                }
            } else if ($picture_remove === '1') {
                $profile->setPicture(null);
            }
        } else {
            $profile->setPictureFile(null);
        }
        $event->setData($profile);
    }

    private function getImageUploader(): ImageUploader
    {
        return $this->image_uploader;
    }
}
