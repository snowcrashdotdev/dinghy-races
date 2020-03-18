<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Repository\PersonalBestRepository;
use App\Repository\GameRepository;

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
    public function refresh(PersonalBestRepository $pbRepo, GameRepository $gamesRepo)
    {
        $games = $gamesRepo->findAllHavingScores();
        $manager = $this->getDoctrine()->getManager();

        foreach ($games as $game) {
            $scores = $game->getScores();
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
}
