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

    public function preUpdate(Score $score, PreUpdateEventArgs $args)
    {
        $score = $args->getObject();
        if ($args->hasChangedField('points')) {
            $old_points = $args->getOldValue('points');
            $new_points = $args->getNewValue('points');

            if ($new_points > $old_points) {
                $score->setUpdatedAt(date_create('NOW'));
                $history = $score->getPointsHistory();
                $history[] = $args->getNewValue('points');
                $score->setPointsHistory($history);
            }
        }

        if ($args->hasChangedField('screenshot')) {
            $old_screenshot = $args->getOldValue('screenshot');

            if ($old_screenshot) {
                $old_path = $this->getScreenshotDir() . '/' . $old_screenshot;

                if ($this->getFilesystem()->exists($old_path)) {
                    $this->getFilesystem()->remove($old_path);
                    $old_name = pathinfo($old_path, PATHINFO_FILENAME);
                    $old_ext = pathinfo($old_path, PATHINFO_EXTENSION);
                
                    foreach(ImageUploader::IMAGE_SIZES as $size) {
                        $size_path = $this->getScreenshotDir()
                        .'/'.$old_name.ImageUploader::SIZE_PREFIX.$size.'.'.$old_ext; 
                        $this->getFilesystem()->remove($size_path);
                    }
                }
            }
        }

        if ($args->hasChangedField('replay')) {
            $old_replay = $args->getOldValue('replay');

            if ($old_replay) {
                $old_path = $this->getReplayDir() . '/' . $old_replay;

                if ($this->getFilesystem()->exists($old_path)) {
                    $this->getFilesystem()->remove($old_path);
                }
            }
        }

        if ($args->hasChangedField('points') && $score instanceof TournamentScore) {
            $old_score = $args->getOldValue('points');
            $new_score = $args->getNewValue('points');

            if ($new_score > $old_score) {
                $this->getScoreKeeper()->scoreGame(
                    $score->getTournament(),
                    $score->getGame()
                );
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