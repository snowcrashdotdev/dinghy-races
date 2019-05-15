<?php
namespace App\Event;

use App\Event\NewScoreEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\Common\Persistence\ObjectManager;

class ScoreSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(ObjectManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getManager()
    {
        return $this->em;
    }

    public static function getSubscribedEvents()
    {
        return [
            NewScoreEvent::NAME => 'onNewScore'
        ];
    }

    public function onNewScore(NewScoreEvent $event) {
        $em = $this->getManager();

        $scores = $em->getRepository('App\Entity\Score')
            ->findBy([
                'game' => $event->getScore()->getGame(),
                'tournament' => $event->getScore()->getTournament()
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