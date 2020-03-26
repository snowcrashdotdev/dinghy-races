<?php
namespace App\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Filesystem\Filesystem;
use App\Service\ImageUploader;

class ProfileChange
{
    public function __construct(String $upload_dir)
    {
        $this->upload_dir = $upload_dir;
        $this->fs = new Filesystem();
    }
    public function preUpdate($profile, PreUpdateEventArgs $args)
    {
        if (
            $args->hasChangedField('picture') &&
            $args->getOldValue('picture') !== null
        ) {
            $prev_full_filename = $args->getOldValue('picture');
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

    public function getUploadDir()
    {
        return $this->upload_dir;
    }

    public function getFilesystem()
    {
        return $this->fs;
    }
}