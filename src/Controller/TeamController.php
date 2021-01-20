<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\Tournament;
use App\Entity\User;
use App\Form\TeamType;
use App\Form\RosterType;
use App\Repository\TournamentScoreRepository;
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

            return $this->redirectToRoute('team_edit', [
                'id' => $team->getId()
            ]);
        }

        return $this->render('team/new.html.twig', [
            'tournament' => $tournament,
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="team_show", methods={"GET"})
     */
    public function show(Team $team, TournamentScoreRepository $scores): Response
    {
        $points_per_game = $scores->findTeamScoresPerGame($team);

        return $this->render('team/show.html.twig', [
            'team' => $team,
            'points_per_game' => $points_per_game
        ]);
    }

    /**
     * @Route("/{id}/edit", name="team_edit", methods={"GET","POST", "PATCH"})
     * @IsGranted("ROLE_TO")
     */
    public function edit(Request $request, Team $team): Response
    {
        $tournament = $team->getTournament();

        $eligiblePlayers = $this->getDoctrine()
            ->getRepository('App\Entity\DraftEntry')
            ->findEligiblePlayers($tournament)
        ;

        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Your changes have been saved!');
        }

        $rosterForm = $this->createForm(RosterType::class, $team);
        $rosterForm->handleRequest($request);
        if ($rosterForm->isSubmitted() && $rosterForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'message' => 'The roster has been updated.'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Your changes were not saved.'
                ]);
            }
        }

        return $this->render('team/edit.html.twig', [
            'tournament' => $tournament,
            'team' => $team,
            'eligible_players' => $eligiblePlayers,
            'form' => $form->createView(),
            'roster_form' => $rosterForm->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="team_delete", methods={"DELETE"})
     * @IsGranted("ROLE_TO")
     */
    public function delete(Request $request, Team $team): Response
    {
        if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->request->get('_token'))) {
            $tournament = $team->getTournament();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($team);
            $entityManager->flush();

            return $this->redirectToRoute('tournament_show', [
                'id' => $tournament->getId()
            ]);
        }

        return $this->redirectToRoute('tournament_index');
    }

    /**
     * @Route("/{id}/send/{user}/{receivingTeam}",
     * name="team_send",
     * methods={"POST"})
     * @IsGranted("ROLE_TO")
     * @Entity("user", expr="repository.findOneBy({username: user})")
     */
    public function send(Request $request, Team $team, Team $receivingTeam, User $user)
    {
        if ($this->isCsrfTokenValid('send'.$team->getId(), $request->request->get('_token'))) {
            $appearance = $this->getDoctrine()
                ->getRepository('App\Entity\TournamentUser')
                ->findOneBy([
                    'user' => $user,
                    'tournament' => $team->getTournament()
                ])
            ;
            $team->removeMember($appearance);
            $receivingTeam->addMember($appearance);
            $this->getDoctrine()->getManager()->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'message' => "${user} succesfully sent to ${receivingTeam}!"
                ]);
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => false,
                'message' => 'Unable to complete player transfer.'
            ]);
        }

        return $this->redirectToRoute('team_edit', [
            'id' => $team->getId()
        ]);
    }
}
