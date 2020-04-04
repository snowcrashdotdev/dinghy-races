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
    public function show(Tournament $tournament, TournamentScoreRepository $scoreRepository, TwitchChecker $twitch): Response
    {
        $recentScores = [];
        $liveStreams  = [];
        $topTeam = null;
        $topPlayer = null;

        if ($tournament->isInProgress()) {
            $recentScores = $scoreRepository->findBy([
                'tournament' => $tournament],
                ['updated_at' => 'DESC'],
                5
            );

            $liveStreams = $twitch->getLiveStreams($tournament);
        }

        if (!$tournament->isUpcoming()) {
            $topTeam = $scoreRepository->findTeamScores($tournament, null, 1);
            $topPlayer = $scoreRepository->findIndividualScores($tournament, 1);
        }

        return $this->render('tournament/show.html.twig', [
            'tournament' => $tournament,
            'top_team' => $topTeam,
            'top_player' => $topPlayer,
            'recent_scores' => $recentScores,
            'live_streams' => $liveStreams
        ]);
    }

    /**
     * @Route("/{tournament}/leaderboards/team", name="leaderboard_team", methods={"GET"})
     */
    public function team_leaderboard(Tournament $tournament)
    {
        $teamScores = $this->getDoctrine()
            ->getRepository('App\Entity\TournamentScore')
            ->findTeamScores($tournament);

        return $this->render('tournament/leaderboard.team.html.twig', [
            'tournament' => $tournament,
            'teamScores' => $teamScores
        ]);
    }

    /**
     * @Route("/{tournament}/leaderboards/individual", name="leaderboard_individual", methods={"GET"})
     */
    public function individual_leaderboard(Tournament $tournament)
    {
        if ($currentUser = $this->getUser()) {
            $user = $currentUser->getId();
        } else {
            $user = null;
        }

        $scores = $this->getDoctrine()
            ->getRepository('App\Entity\TournamentScore')
            ->findIndividualScores($tournament);

        return $this->render('tournament/leaderboard.individual.html.twig', [
            'user' => $user,
            'tournament' => $tournament,
            'scores' => $scores,
        ]);
    }

    /**
     * @Route("/{id}/.json", name="tournament_json", methods={"GET"})
     */
    public function show_json(Tournament $tournament)
    {
        $scores = $this->getDoctrine()
            ->getRepository('App\Entity\TournamentScore')
            ->findByTournamentPublic($tournament)
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
        $scores = $this->getDoctrine()
            ->getRepository('App\Entity\TournamentScore')
            ->findByTournamentPublic($tournament)
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
     * @Route("/{id}/edit", name="tournament_edit", methods={"GET","POST","PATCH"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_TO')")
     */
    public function edit(Request $request, Tournament $tournament): Response
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
            $this->getDoctrine()->getManager()->flush();

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
