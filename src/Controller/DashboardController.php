<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TournamentRepository;
use App\Repository\TournamentScoreRepository;
use App\Repository\TournamentUserRepository;

/**
 * @Route("/dashboard")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function index(TournamentRepository $tournaments, TournamentScoreRepository $scores, TournamentUserRepository $users)
    {
        $user = $this->getUser();

        if (empty($user)) {
            return $this->redirectToRoute('tournament_index');
        }

        $in_progress_tournaments = $tournaments->findForUser($user, 'IN_PROGRESS');

        if (empty($in_progress_tournaments)) {
            return $this->redirectToRoute('tournament_index');
        }

        $dashboard_data = [];

        $all_scores = [];
        foreach($in_progress_tournaments as $tournament) {
            $all_scores = array_merge( $tournament->getScores()->toArray(), $all_scores );
        }
        
        $user_scores = $scores->findActiveForUser($user);
        $stddev = $scores->findScoreStdDev($in_progress_tournaments);

        $rivals = $users->findTournamentRivals($user, $in_progress_tournaments);

        $dashboard_data = [
            'user' => [
                'username' => $user->getUsername(),
                'scores' => $user_scores
            ],
            'scores' => $all_scores,
            'stddev' => $stddev,
            'rivals' => $rivals
        ];

        return $this->render('dashboard/index.html.twig', [
            'dashboard_data' => $dashboard_data,
            'tournaments' => $in_progress_tournaments
        ]);
    }
}
