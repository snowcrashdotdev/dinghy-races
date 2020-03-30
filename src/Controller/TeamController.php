<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\Tournament;
use App\Form\TeamType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

/**
 * @Route("/teams")
 */
class TeamController extends AbstractController
{
    /**
     * @Route("/new/{tournament}", name="team_new", methods={"GET","POST"})
     * @Entity("tournament", expr="repository.find(tournament)")
     * @IsGranted("ROLE_TO")
     */
    public function new(Request $request, Tournament $tournament): Response
    {
        $team = new Team();
        $tournament->addTeam($team);
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('tournament_show', ['id'=>$team->tournament->getId()]);
        }

        return $this->render('team/new.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{team}", name="team_show", methods={"GET"})
     */
    public function show(Team $team): Response
    {
        $scoreRepo = $this->getDoctrine()->getRepository('App\Entity\TournamentScore');
        $leaderboard = $scoreRepo->findTeamLeaderboard($team);
        $points_per_game = $scoreRepo->findTeamScoresPerGame($team);

        return $this->render('team/show.html.twig', [
            'team' => $team,
            'leaderboard' => $leaderboard,
            'points_per_game' => $points_per_game
        ]);
    }

    /**
     * @Route("/{id}/edit", name="team_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_TO")
     */
    public function edit(Request $request, Team $team): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tournament_show', ['id'=>$team->getTournament()->getId()]);
        }

        return $this->render('team/edit.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="team_delete", methods={"DELETE"})
     * @IsGranted("ROLE_TO")
     */
    public function delete(Request $request, Team $team): Response
    {
        if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($team);
            $entityManager->flush();
        }

        return $this->redirectToRoute('team_index');
    }
}
