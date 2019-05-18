<?php
namespace App\Event;

use App\Entity\Score;
use App\Service\FileUploader;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadListener
{
    private $uploader;

    public function __construct(FileUploader $uploader)
    {
        $this->uploader = $uploader;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $score = $args->getEntity();
        $this->uploadFile($score);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $score = $args->getEntity();
        $this->uploadFile($score);

    }

    private function uploadFile($score)
    {
        if (!$score instanceof Score) {
            return;
        }

        $file = $score->getScreenshot();

        if ($file instanceof UploadedFile) {
            // New File
            $filename = $this->uploader->upload($file);
            $score->setScreenshot($filename);

        } elseif ($file instanceof File) {
            // Old File
            $score->setScreenshot($file->getFilename());
        }
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Score) {
            return;
        }

        if ($fileName = $entity->getScreenshot()) {
            $entity->setScreenshot(new File($this->uploader->getTargetDirectory().'/'.$fileName));
        }
    }
}