<?php
namespace App\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Filesystem\Filesystem;
use App\Service\ImageUploader;

class ScoreChange
{
    public function __construct(String $upload_dir)
    {
        $this->upload_dir = $upload_dir;
        $this->fs = new Filesystem();
    }
    public function preUpdate($score, PreUpdateEventArgs $args)
    {
        if ($args->hasChangedField('points')) {
            $history = $score->getPointsHistory();
            $history[] = $args->getNewValue('points');
            $new_history = array_unique($history);
            sort($new_history, SORT_NUMERIC);
            
            $score->setPointsHistory($new_history);
            $score->setUpdatedAt(new \DateTime('NOW'));
        }

        if (
            $args->hasChangedField('screenshot') &&
            $args->getOldValue('screenshot') !== null
        ) {
            $prev_full_filename = $args->getOldValue('screenshot');
            $prev_screenshot_path = $this->getUploadDir() . '/' . $prev_full_filename;
            if ($this->getFilesystem()->exists($prev_screenshot_path)) {
                $this->getFilesystem()->remove($prev_screenshot_path);
                $prev_name = pathinfo($prev_screenshot_path, PATHINFO_FILENAME);
                $prev_ext = pathinfo($prev_screenshot_path, PATHINFO_EXTENSION);
                
                foreach(ImageUploader::IMAGE_SIZES as $size) {
                    $size_path = $this->getUploadDir()
                        .'/'.$prev_name.ImageUploader::SIZE_PREFIX.$size.'.'.$prev_ext; 
                    $this->getFilesystem()->remove($size_path);
                }
            }
        }
    }

    public function prePersist($score, LifecycleEventArgs $args)
    {
        $now = new \DateTime('NOW');
        $score->setCreatedAt($now);
        $score->setUpdatedAt($now);
    }

    public function getUploadDir()
    {
        return $this->upload_dir;
    }

    public function getFilesystem()
    {
        return $this->fs;
    }
}