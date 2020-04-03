<?php
namespace App\EventListener;

use App\Entity\Game;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Filesystem\Filesystem;
use App\Service\ImageUploader;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class GameListener
{
    public function __construct(String $upload_dir)
    {
        $this->upload_dir = $upload_dir;
        $this->fs = new Filesystem();
    }
    public function preUpdate(Game $game, PreUpdateEventArgs $args)
    {
        if (
            $args->hasChangedField('marquee') &&
            $args->getOldValue('marquee') !== null
        ) {
            $prev_full_filename = $args->getOldValue('marquee');
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

    public function postLoad(Game $game)
    {
        try {
            $game->setMarqueeFile(
                new File(
                    $this->getUploadDir() . '/' . $game->getMarquee()
                )
            );
        } catch (FileException $e) {
            $game->setMarqueeFile(null);
        }
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