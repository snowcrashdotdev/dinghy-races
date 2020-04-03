<?php
namespace App\EventListener;

use App\Entity\Score;
use App\Entity\TournamentScore;
use App\Service\ScoreKeeper;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Filesystem\Filesystem;
use App\Service\ImageUploader;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ScoreListener
{
    public function __construct(string $screenshot_dir, string $replay_dir, ScoreKeeper $score_keeper)
    {
        $this->screenshot_dir = $screenshot_dir;
        $this->replay_dir = $replay_dir;
        $this->fs = new Filesystem();
        $this->score_keeper = $score_keeper;
    }

    public function preUpdate($score, PreUpdateEventArgs $args)
    {
        if ($args->hasChangedField('points')) {
            $history = $score->getPointsHistory();
            $history[] = $args->getNewValue('points');
            $score->setPointsHistory($history);

            if (is_a($score, TournamentScore::class)) {
                $tournament = $score->getTournament();
                $game = $score->getGame();

                $this->getScoreKeeper()
                    ->scoreGame($tournament, $game)
                ;
            }
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

    public function getScoreKeeper(): ScoreKeeper
    {
        return $this->score_keeper;
    }
}