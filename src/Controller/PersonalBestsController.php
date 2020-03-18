<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\PersonalBest;
use App\Repository\GameRepository;
use App\Repository\PersonalBestRepository;

/**
 * @Route("/permanent-boards")
 */
class PersonalBestsController extends AbstractController
{
    /**
     * @Route("/", name="pb_index")
     */
    public function index(GameRepository $gamesRepo)
    {
        $games = $gamesRepo->findAllHavingScores();

        return $this->render('personal_bests/index.html.twig', [
            'games' => $games
        ]);
    }

    /**
     * @Route("/{game}", name="pb_show")
     */
    public function show(String $game, GameRepository $gamesRepo)
    {
        $game = $gamesRepo->findOneBy(['name' => $game]);

        return $this->render('personal_bests/show.html.twig', [
            'game' => $game
        ]);
    }
}
