<?php
namespace App\EventListener;

use App\Entity\Score;
use App\Entity\TournamentScore;
use App\Entity\PersonalBest;
use App\Service\ScoreKeeper;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
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

    public function postPersist(Score $score, LifecycleEventArgs $args)
    {
        if ($score instanceof TournamentScore) {
            $this->getScoreKeeper()->scoreGame(
                $score->getTournament(),
                $score->getGame()
            );
            $args->getObjectManager()->flush();
        }
        
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

            if ($new_score > $old_score && $new_score !== 0) {
                $this->getScoreKeeper()->scoreGame(
                    $score->getTournament(),
                    $score->getGame()
                );
            }
        }
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $manager = $args->getEntityManager();
        $uow = $manager->getUnitOfWork();

        foreach($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof TournamentScore) {
                $this->syncPersonalBest($entity, $manager, $uow);
            }
        }

        foreach($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof TournamentScore) {
                $this->syncPersonalBest($entity, $manager, $uow);
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

    private function syncPersonalBest(TournamentScore $score, EntityManager $manager, UnitOfWork $uow)
    {
        $game = $score->getGame();
        $user = $score->getUser();
        $changeSet = $uow->getEntityChangeSet($score);
        $metaData = $manager->getClassMetaData(PersonalBest::class);

        if(empty(
            $personalBest = $manager->getRepository('App\Entity\PersonalBest')
                ->findOneBy([
                    'game' => $game,
                    'user' => $user
                ])
            )
        ) {
            $new_personalBest = $this->newPersonalBestFrom($score);
            $manager->persist($new_personalBest);
            $uow->computeChangeSet($metaData, $new_personalBest);
        } elseif (
            isset($changeSet['points']) &&
            $changeSet['points'][0] < $changeSet['points'][1] &&
            $changeSet['points'][1] > $personalBest->getPoints()
        ) {
            $new_personalBest = $this->newPersonalBestFrom($score, $personalBest);
            $uow->computeChangeSet($metaData, $new_personalBest);
        }
    }

    private function newPersonalBestFrom(TournamentScore $score, ?PersonalBest $personalBest=null): PersonalBest
    {
        if (empty($personalBest)) {
            $personalBest = new PersonalBest();
            $score->getGame()->addPersonalBest($personalBest);
            $score->getUser()->getUser()->addPersonalBest($personalBest);
        }
        $personalBest->setPoints($score->getPoints());

        $history = $personalBest->getPointsHistory();
        $history[] = $score->getPoints();
        $personalBest->setPointsHistory($history);

        $personalBest->setScreenshot($score->getScreenshot());
        $personalBest->setVideoUrl($score->getVideoUrl());
        $personalBest->setReplay($score->getReplay());
        $personalBest->setComment($score->getComment());

        return $personalBest;
    }
}