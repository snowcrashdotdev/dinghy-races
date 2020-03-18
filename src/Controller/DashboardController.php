<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Repository\PersonalBestRepository;
use App\Entity\Tournament;
use App\Service\ScoreKeeper;

/**
 * @Route("/dashboard")
 * @Security("is_granted('ROLE_USER')")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function index()
    {
        $tournaments = $this->getUser()->getTournaments()->filter(function($tournament) { return $tournament->isInProgress(); });

        return $this->render('dashboard/index.html.twig', [
            'tournaments' => $tournaments
        ]);
    }

    /**
     * @Route("/permanent-boards/refresh", name="pb_refresh")
     * @Security("is_granted('ROLE_TO')")
     */
    public function pb_refresh(PersonalBestRepository $pbRepo)
    {
        $games = $this->getDoctrine()
            ->getRepository('App\Entity\Game')
            ->findAllHavingScores();
        $manager = $this->getDoctrine()->getManager();

        foreach ($games as $game) {
            $scores = $this->getDoctrine()
                ->getRepository('App\Entity\Score')
                ->findBy([
                    'game' => $game,
                    'auto_assigned' => false,
                ])
            ;   
            foreach($scores as $score) {
                $pb = $pbRepo->findOneBy([
                    'game' => $game->getId(),
                    'user' => $score->getUser() ->getId()
                ]);
                if (empty($pb)) {
                    $pb = new PersonalBest();
                    $pb->setCreatedAt(new \DateTime('now'));
                    $pb->setUpdatedAt(new \DateTime('now'));
                    $pb->setUser($score->getUser());
                    $pb->setGame($game);
                    $pb->setPoints($score->getPoints());
                    $pb->setPointsHistory([$score->getPoints()]);
                    $pb->setScreenshot($score->getScreenshot());
                    $pb->setVideoUrl($score->getVideoUrl());
                    $pb->setComment($score->getComment());
                    $manager->persist($pb);
                }
            }
            $manager->flush();
        }

        return $this->redirectToRoute('pb_index');
    }

    /**
     * @Route("/tournaments/refresh/{tournament}", name="tournament_refresh")
     * @Security("is_granted('ROLE_TO')")
     */
    public function tournament_refresh(Tournament $tournament)
    {
        $manager = $this->getDoctrine()->getManager();
        $scorekeeper = new ScoreKeeper($tournament, $manager);
        $scorekeeper->scoreTournament();

        return $this->redirectToRoute('tournament_show', ['id' => $tournament->getId()]);
    }
}
