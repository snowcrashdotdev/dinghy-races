<?php
namespace App\EventListener;

use App\Entity\Score;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Filesystem\Filesystem;
use App\Service\ImageUploader;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ScoreListener
{
    public function __construct(string $screenshot_dir, string $replay_dir)
    {
        $this->screenshot_dir = $screenshot_dir;
        $this->replay_dir = $replay_dir;
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
            $prev_screenshot_path = $this->getScreenshotDir() . '/' . $prev_full_filename;
            if ($this->getFilesystem()->exists($prev_screenshot_path)) {
                $this->getFilesystem()->remove($prev_screenshot_path);
                $prev_name = pathinfo($prev_screenshot_path, PATHINFO_FILENAME);
                $prev_ext = pathinfo($prev_screenshot_path, PATHINFO_EXTENSION);
                
                foreach(ImageUploader::IMAGE_SIZES as $size) {
                    $size_path = $this->getScreenshotDir()
                        .'/'.$prev_name.ImageUploader::SIZE_PREFIX.$size.'.'.$prev_ext; 
                    $this->getFilesystem()->remove($size_path);
                }
            }
        }
    }

    public function postLoad(Score $score)
    {
        try {
            $score->setScreenshotFile(
                new File(
                    $this->getScreenshotDir() . '/' . $score->getScreenshot()
                )
            );
        } catch (FileException $e) {
            $score->setScreenshotFile(null);
        }

        try {
            $score->setReplayFile(
                new File(
                    $this->getReplayDir() . '/' . $score->getReplay()
                )
            );
        } catch (FileException $e) {
            $score->setReplayFile(null);
        }

    }

    public function getScreenshotDir()
    {
        return $this->screenshot_dir;
    }

    public function getReplayDir()
    {
        return $this->replay_dir;
    }

    public function getFilesystem()
    {
        return $this->fs;
    }
}