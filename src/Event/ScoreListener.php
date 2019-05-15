<?php
namespace App\Event;

use App\Entity\Score;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class ScoreListener
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Score) {
            return;
        }

        $em = $args->getObjectManager();

        $scores = $em->getRepository('App\Entity\Score')
            ->findBy([
                'game' => $entity->getGame(),
                'tournament' => $entity->getTournament()
            ])
        ;
        
        foreach($scores as $score) {
            $rank = $em->getRepository('App\Entity\Score')
                ->findCountGreaterThanPoints($score)
            ;
            $score->setRank($rank);
            $em->persist($score);
        }
        $em->flush();

        $teams = $score->getTournament()->getTeams();
        foreach($teams as $team) {
            $total = 0;
            $scores = $team->getScores();
            foreach($scores as $score) {
                $rank = $score->getRank();
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
        }
        $em->flush();
    }
}