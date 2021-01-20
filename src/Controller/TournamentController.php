<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Form\TournamentType;
use App\Entity\Draft;
use App\Entity\TournamentScoring;
use App\Form\GameCollectionType;
use App\Repository\TournamentRepository;
use App\Repository\TournamentScoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\TwitchChecker;
use Doctrine\ORM\Query;

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
        $tournaments = $tournamentRepository->findAll();

        return $this->render('tournament/index.html.twig', [
            'tournaments' => $tournaments
        ]);
    }

    /**
     * @Route("/new", name="tournament_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_TO')")
     */
    public function new(Request $request): Response
    {
        $tournament = new Tournament();
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $draft = new Draft();
            $scoring = new TournamentScoring();
            $tournament->setDraft($draft);
            $tournament->setScoring($scoring);
            $scoring->setDeadline($tournament->getEndDate());

            $this->getDoctrine()->getManager()->persist($tournament);
            $this->getDoctrine()->getManager()->flush();

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
    public function show(Tournament $tournament, TournamentScoreRepository $scores, TwitchChecker $twitch): Response
    {
        $live_streams  = [];
        $top_team = $tournament->getTeams()->first();
        $top_five = $tournament->getUsers()->slice(0, 5);
        $user_scores = [];
        $high_scores = $scores->findHighScores($tournament);
        $recent_scores = [];

        if ($this->isGranted('ROLE_USER') && ! $tournament->isUpcoming()) {
            $user_scores = $scores->findUserScores($this->getUser(), $tournament);
        }

        if ($tournament->isInProgress()) {
            $live_streams = $twitch->getLiveStreams($tournament);

            $recent_scores = $scores->findBy(
                [ 'tournament' => $tournament ],
                [ 'updated_at' => 'DESC' ],
                5, 0
            );
        }

        return $this->render('tournament/show.html.twig', [
            'tournament' => $tournament,
            'top_team' => $top_team,
            'top_five' => $top_five,
            'live_streams' => $live_streams,
            'high_scores' => $high_scores,
            'recent_scores' => $recent_scores,
            'user_scores' => $user_scores
        ]);
    }

    /**
     * @Route("/{id}/leaderboards/team", name="team_leaderboard", methods={"GET"})
     */
    public function team_leaderboard(Tournament $tournament)
    {
        return $this->render('tournament/_results.team.html.twig', [
            'tournament' => $tournament
        ]);
    }

    /**
     * @Route("/{id}/leaderboards/individual", name="individual_leaderboard", methods={"GET"})
     */
    public function individual_leaderboard(Tournament $tournament, TournamentScoreRepository $tournamentScoreRepository)
    {
        return $this->render('tournament/_results.individual.html.twig', [
            'tournament' => $tournament
        ]);
    }

    /**
     * @Route("/{id}/.json", name="tournament_json", methods={"GET"})
     */
    public function show_json(Tournament $tournament)
    {
        $scores = $tournament->getScores()
            ->map(function($score) {
                return $score->getPublicData();
            })
            ->toArray()
        ;

        return $this->json([
            'data' => $scores
        ]);
    }

    /**
     * @Route("/{id}/.csv", name="tournament_csv", methods={"GET"})
     */
    public function show_csv(Tournament $tournament)
    {
        $scores = $tournament->getScores()
            ->map(function($score) {
                return $score->getPublicData();
            })
            ->toArray()
        ;

        $csv = $this->get('serializer')->encode($scores, 'csv');
                $response = new Response($csv);
                $disposition = HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_ATTACHMENT,
                    $tournament->getTitle() . '-scores.csv'
                );
                $response->headers->set('Content-Type', 'text/csv');
                $response->headers->set('Content-Disposition', $disposition);

                return $response;
    }

    /**
     * @Route("/{id}/stream")
     */
    public function stream_kit(Request $request, Tournament $tournament)
    {
        if (! $tournament->isInProgress()) {
            throw $this->createNotFoundException('That tournament has ended.');
        }

        $query = $request->query->all();

        if (empty($query) || ! isset($query['username'])) {
            throw $this->createNotFoundException('Could not find stream kit for that user and tournament.');
        }

        return $this->render('tournament/_stream.html.twig', [
            'tournament' => $tournament,
            'query' => $query
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tournament_edit", methods={"GET","POST","PATCH"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_TO')")
     */
    public function edit(Request $request, Tournament $tournament, TournamentScoreRepository $scores): Response
    {
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Tournament details updated!');
            $this->getDoctrine()->getManager()->flush();
        }

        $gamesForm = $this->createForm(GameCollectionType::class, $tournament);
        $gamesForm->handleRequest($request);
        if ($gamesForm->isSubmitted() && $gamesForm->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            $invalidScores = $scores->findBy([
                'tournament' => null,
            ]);

            if (! empty($invalidScores)) {
                foreach($invalidScores as $invalid) {
                    $manager->remove($invalid);
                }
                $manager->flush();
            }

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'message' => 'Tournament games updated!'
                ]);
            } else {
                $this->addFlash('success', 'Games added to tournament');
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => false,
                'message' => 'Unable to update games list.'
            ]);
        }

        return $this->render('tournament/edit.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView(),
            'games_form' => $gamesForm->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="tournament_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
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
