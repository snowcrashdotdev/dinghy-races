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

        $in_progress_tournaments = $tournaments->findForUser($user, 'IN_PROGRESS');

        if (empty($in_progress_tournaments)) {
            return $this->redirectToRoute('tournament_index');
        }

        $dashboard_data = [];

        foreach($in_progress_tournaments as $tournament) {
            $all_scores = array_merge( $tournament->getScores()->toArray() );
        }
        
        $user_scores = $scores->findActiveForUser($user);

        $dashboard_data['user'] = [
            'username' => $user->getUsername(),
            'scores' => $user_scores
        ];

        $dashboard_data['scores'] = $all_scores;

        return $this->render('dashboard/index.html.twig', [
            'dashboard_data' => $dashboard_data
        ]);
    }
}
