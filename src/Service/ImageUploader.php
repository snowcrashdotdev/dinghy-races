<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader
{
    private $targetDirectory;
    public const IMAGE_SIZES = [500, 300, 100];
    public const MAX_IMAGE_SIZE = 800;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file)
    {
        $og_filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safe_filename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $og_filename);
        $file_name = $safe_filename.'-'.uniqid();
        $file_ext = '.'.$file->guessExtension();
        $full_file_name = $file_name.$file_ext;

        try {
            $file = $file->move($this->getTargetDirectory(), $full_file_name);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        $upload_path = $file->getRealPath();
        $img = new \Imagick($upload_path);
        $img->scaleImage($this::MAX_IMAGE_SIZE, 0);
        $img->writeImage($upload_path);

        foreach($this::IMAGE_SIZES as $size) {
            $new_path = $this->getTargetDirectory()
                .'/'.$file_name.'x'.$size.$file_ext;
            $img->scaleImage($size, 0);
            $img->writeImage($new_path);
        }
        $img->clear();

        return $full_file_name;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}