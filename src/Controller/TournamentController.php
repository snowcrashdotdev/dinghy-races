<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Form\TournamentType;
use App\Entity\Game;
use App\Entity\Draft;
use App\Repository\TournamentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/tournaments")
 */
class TournamentController extends AbstractController
{
    /**
     * @Route("/", name="tournament_index", methods={"GET"})
     */
    public function index(TournamentRepository $tournamentRepository): Response
    {
        $today = new \DateTime('NOW');
        $tournaments = $tournamentRepository->findAll();
        $upcoming = array_filter($tournaments, function($t) use ($today) {
            return ( $t->getStartDate() > $today );
        });
        $in_progress = array_filter($tournaments, function($t) use ($today) {
            return ( $t->getStartDate() < $today && $today < $t->getEndDate() );
        });
        $past = array_filter($tournaments, function($t) use ($today) {
            return ( $t->getEndDate() < $today );
        });

        return $this->render('tournament/index.html.twig', [
            'upcoming_tournaments' => $upcoming,
            'in_progress_tournaments' => $in_progress,
            'past_tournaments' => $past
        ]);
    }

    /**
     * @Route("/new", name="tournament_new", methods={"GET","POST"})
     * @isGranted("ROLE_ADMIN")
     */
    public function new(Request $request): Response
    {
        $tournament = new Tournament();
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $draft = new Draft();
            $draft->setTournament($tournament);
            $entityManager->persist($draft);
            foreach($tournament->getTeams() as $team) {
                foreach($team->getMembers() as $user) {
                    $tournament->addUser($user);
                }
            }
            $entityManager->persist($tournament);
            $entityManager->flush();

            return $this->redirectToRoute('tournament_edit', ['id'=>$tournament->getId()]);
        }

        return $this->render('tournament/new.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="tournament_show", methods={"GET"})
     */
    public function show(Tournament $tournament): Response
    {
        $topScorer = $this->getDoctrine()
            ->getRepository('App\Entity\Score')
            ->findIndividualScores($tournament, 1)
        ;

        $latestScores = $this->getDoctrine()
            ->getRepository('App\Entity\Score')
            ->findBy(['tournament' => $tournament],['date_updated'=>'DESC'],5,0);

        return $this->render('tournament/show.html.twig', [
            'tournament' => $tournament,
            'latestScores' => $latestScores
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tournament_edit", methods={"GET","POST"})
     * @isGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Tournament $tournament): Response
    {
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            foreach($tournament->getTeams() as $team) {
                foreach($team->getMembers() as $user) {
                    $tournament->addUser($user);
                }
            }
            $em->persist($tournament);
            $em->flush();

            return $this->redirectToRoute('tournament_index', [
                'id' => $tournament->getId(),
            ]);
        }

        return $this->render('tournament/edit.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/scores/{game}", name="tournament_scores", methods={"GET","POST"})
     */
    public function game(Request $request, Tournament $tournament, Game $game)
    {
        $scores = $this->getDoctrine()
            ->getRepository('App\Entity\Score')
            ->findByGameAndTournament($tournament->getId(), $game);

        return $this->render('score/summary.html.twig', [
            'scores' => $scores,
            'game' => $game,
            'tournament' => $tournament
            ]);
    }

    /**
     * @Route("/{tournament}/leaderboards/team", name="leaderboard_team", methods={"GET"})
     */
    public function team_leaderboard(Request $request, Tournament $tournament)
    {
        return $this->render('tournament/leaderboard.team.html.twig', [
            'tournament' => $tournament
        ]);
    }

    /**
     * @Route("/{tournament}/leaderboards/individual", name="leaderboard_individual", methods={"GET"})
     */
    public function individual_leaderboard(Request $request, Tournament $tournament)
    {
        return $this->render('tournament/leaderboard.individual.html.twig', [
            'tournament' => $tournament,
            'scores' => $tournament->scoreTournament('ind'),
        ]);
    }

    /**
     * @Route("/{id}", name="tournament_delete", methods={"DELETE"})
     * @isGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Tournament $tournament): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tournament->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($tournament);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tournament_index');
    }
}
