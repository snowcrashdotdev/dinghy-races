<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TournamentRepository;
use App\Repository\TournamentScoreRepository;

/**
 * @Route("/dashboard")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function index(TournamentRepository $tournaments, TournamentScoreRepository $scores)
    {
        $user = $this->getUser();

        if (empty($user)) {
            return $this->redirectToRoute('tournament_index');
        }

        $user_scores = null;
        $upcoming_tournaments = $tournaments->findForUser($user);
        $in_progress_tournaments = $tournaments->findForUser($user, 'IN_PROGRESS');

        if (!empty($in_progress_tournaments)) {
            $user_scores = $scores->findActiveForUser($user);
        }

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'user_scores' => $user_scores,
            'upcoming_tournaments' => $upcoming_tournaments,
            'in_progress_tournaments' => $in_progress_tournaments
        ]);
    }
}
