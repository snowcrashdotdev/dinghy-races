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
        $scores = $tournament->scoreTournament();

        foreach($scores as $score) {
            $team = $score['team'];
            $points = $score['points'];
            $team->setPoints($points);
            $em->persist($team);
        }
        $em->flush();
    }
}