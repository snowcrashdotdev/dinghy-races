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
        if (!method_exists($entity = $args->getObject(), 'getGame')) {
            return;
        }

        $em = $args->getObjectManager();
        $game = $entity->getGame();
        $tournament = $entity->getTournament();
        $teams = $tournament->getTeams();

        foreach($teams as $team) {
            $scores = $team->getScores();
            $total = 0;
            foreach($scores as $score) {
                $rank = $tournament->getScoreRank($score);
                if ($rank >= 17) {
                    $total += (40 - ($rank - 17) * 2);
                } elseif ($rank >= 7) {
                    $total += (70 - ($rank - 7) * 3);
                } else {
                    $total += (100 - $rank * 5);
                }
            }
            $team->setScore($total);
            $em->persist($team);
            $em->flush();
        }
    }
}