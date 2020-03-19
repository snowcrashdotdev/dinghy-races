<?php
namespace App\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;

class PersonalBestChange
{
    public function preUpdate(LifecycleEventArgs $args)
    {
        $pb = $args->getObject();
        $changes = $args->getObjectManager()
            ->getUnitOfWork()
            ->getEntityChangeSet($pb)
        ;

        if (array_key_exists('points', $changes)) {
            $history = $pb->getPointsHistory();
            $change = $changes['points'];
            $new_history = array_merge($change,$history);
            $new_history = array_unique($new_history);
            asort($new_history);
            
            $pb->setPointsHistory($new_history);
            $pb->setUpdatedAt(new \DateTime('NOW'));
        }
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $now = new \DateTime('NOW');
        $pb = $args->getObject();
        $pb->setCreatedAt($now);
        $pb->setUpdatedAt($now);
    }
}