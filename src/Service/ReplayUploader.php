<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ReplayUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file)
    {
        $zip = zip_open($file->getRealPath());

        if (is_resource($zip)) {
            do {
                $entry = zip_read($zip);
            } while ($entry
                && !preg_match("/.+\.inp$/", zip_entry_name($entry))
            );
        }

        if ($entry) {
            zip_entry_open($zip, $entry, 'r');
            $zip_header = zip_entry_read($entry, 7);
            zip_entry_close($entry);
        } else {
            return false;
        }

        if ($zip_header !== 'MAMEINP') {
            return false;
        } else {
            $og_filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safe_filename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $og_filename);
            $file_name = $safe_filename.'-'.uniqid();
            $file_ext = '.'.$file->guessExtension();
            $full_file_name = $file_name.$file_ext;
    
            try {
                $file = $file->move($this->getTargetDirectory(), $full_file_name);
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
                return false;
            }
    
            return $full_file_name;
        }
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}