<?php
namespace App\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class PersonalBestChange
{
    public function preUpdate($score, PreUpdateEventArgs $args)
    {
        $changes = $args->getObjectManager()
            ->getUnitOfWork()
            ->getEntityChangeSet($score)
        ;

        if (array_key_exists('points', $changes)) {
            $history = $score->getPointsHistory();
            $change = $changes['points'];
            $new_history = array_merge($change,$history);
            $new_history = array_unique($new_history);
            sort($new_history, SORT_NUMERIC);
            
            $score->setPointsHistory($new_history);
            $score->setUpdatedAt(new \DateTime('NOW'));
        }
    }

    public function prePersist($score, LifecycleEventArgs $args)
    {
        $now = new \DateTime('NOW');
        $score->setCreatedAt($now);
        $score->setUpdatedAt($now);
    }
}