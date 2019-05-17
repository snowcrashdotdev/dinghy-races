<?php
namespace App\Event;

use App\Entity\Score;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class ScoreSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->calculateTeamScore($args);

    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->calculateTeamScore($args);
    }

    public function calculateTeamScore(LifecycleEventArgs $args)
    {
        if (!($entity = $args->getObject()) instanceof Score) {
            return;
        }

        $em = $args->getObjectManager();

        $tournament = $entity->getTournament();
        $teams = $tournament->getTeams()->toArray();

        foreach($teams as $team) {
            $scores = $team->getScores()->toArray();
            $points = 0;
            foreach($scores as $score) {
                $rank = $tournament->getScoreRank($score);
                if ($rank >= 17) {
                    $points += (40 - ($rank - 17) * 2);
                } elseif ($rank >= 7) {
                    $points += (70 - ($rank - 7) * 3);
                } elseif ($rank >= 0) {
                    $points += (100 - $rank * 5);
                }
            }
            $team->setPoints($points);
            $em->persist($team);
        }
        $em->flush();
    }
}