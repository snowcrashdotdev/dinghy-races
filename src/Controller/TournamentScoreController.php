<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\TournamentScore;
use App\Entity\Game;
use App\Entity\Tournament;
use App\Form\ScoreType;
use App\Repository\TournamentScoreRepository;

/**
 * @Route("/scores")
 */
class TournamentScoreController extends AbstractController
{
    /**
     * @Route("/{game_name}/{tournament_id}/new", name="score_new", methods={"GET","POST"})
     * @Entity("tournament", expr="repository.find(tournament_id)")
     * @ParamConverter("game", options={"mapping": {"game_name": "name"}})
     */
    public function new(Request $request, TournamentScoreRepository $scoreRepo, Game $game, Tournament $tournament): Response
    {
        $user = $this->getUser();

        /**
         * Redirect if not logged in, or if not assigned to tournament team
         */
        if (empty($user)) {
            $this->addFlash('notice', 'Log in to update your score.');
            return $this->redirectToRoute('app_login');
        } elseif (empty(
            $team = $user->getTeam($tournament)
        )) {
            $this->addFlash('notice', 'Unable to determine your team.');
            return $this->redirectToRoute('tournament_show', [
                'id' => $tournament->getId()
            ]);
        }

        $score = $scoreRepo->findOneBy([    
            'user' => $user,
            'tournament' => $tournament,
            'game' => $game
        ]);

        if (empty($score)) {
            $score = new TournamentScore();
            $game->addTournamentScore($score);
            $user->addTournamentScore($score);
            $tournament->addScore($score);
            $team->addScore($score);
            if (
                $tournament->getScoring()->getDeadline() &&
                $tournament->getScoring()->getDeadline() > date_create('NOW')
            ) {
                $score->setAutoAssigned(false);
            } else {
                $score->setAutoAssigned(true);
            }

            if ($score->isNoShow()) {
                $score->setPoints(0);
                $this->getDoctrine()->getManager()->persist($score);
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('notice', 'New score submissions are closed.');
                $this->redirectToRoute('tournament_show', [
                    'id' => $tournament->getId()
                ]);
            }
        }

        $form = $this->createForm(ScoreType::class, $score);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Your score was saved!');
            return $this->redirectToRoute('score_show',
                [
                    'tournament_id'=>$score->getTournament()->getId(),
                    'game_name'=>$score->getGame()->getName()
                ]
            );
        }

        return $this->render('score/new.html.twig', [
            'score' => $score,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{game_name}/{tournament_id}", name="score_show")
     * @Entity("tournament", expr="repository.find(tournament_id)")
     * @ParamConverter("game", options={"mapping": {"game_name": "name"}})
     */
    public function show(Tournament $tournament, Game $game, TournamentScoreRepository $repo)
    {
        $scores = $repo->findBy([
            'game' => $game,
            'tournament' => $tournament
            ],
            ['points' => 'DESC']
        );

        $teamTotals = $repo->findTeamScores($tournament, $game);

        return $this->render('score/show.html.twig', [
            'scores' => $scores,
            'game' => $game,
            'tournament' => $tournament,
            'team_totals' => $teamTotals
        ]);
    }
}
