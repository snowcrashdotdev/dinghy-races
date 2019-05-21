<?php
namespace App\Event;

use App\Entity\Tournament;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class TournamentSubscriber implements EventSubscriber
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
        $this->setUserTournament($args);

    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->setUserTournament($args);
    }

    public function setUserTournament(LifecycleEventArgs $args)
    {
        if ( ! ($entity = $args->getObject()) instanceof Tournament) {
            return;
        }

        $em = $args->getObjectManager();

        $teams = $entity->getTeams();

        foreach($teams as $team) {
            foreach($team->getMembers() as $member) {
                $member->addTournament($entity);
                $em->persist($member);
                $tournament->addUser($member);
            }
        }
        $em->persist($tournament);
        $em->flush();
    }
}