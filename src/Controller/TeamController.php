<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/teams")
 */
class TeamController extends AbstractController
{
    /**
     * @Route("/new", name="team_new", methods={"GET","POST"})
     * @isGranted("ROLE_ADMIN")
     */
    public function new(Request $request): Response
    {
        $team = new Team();
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
        return $this->render('team/show.html.twig', [
            'team' => $team,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="team_edit", methods={"GET","POST"})
     * @isGranted("ROLE_ADMIN")
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
     * @isGranted("ROLE_ADMIN")
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

    /**
     * @Route("/promote/{team}/{username}", name="team_promotion", defaults={"username"=""},methods={"POST"})
     */
    public function promote(Request $request, Team $team, string $username)
    {
        $user = $this->getDoctrine()->getRepository('App\Entity\User')
            ->findOneBy(['username' => $username]);
        
        if (!$user) {
            return $this->json([
                'success' => false
            ]);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $demote = $request->query->get('demote');

        if ($demote) {
            $team->removeCaptain($user->getId());
        } elseif (!$demote) {
            $team->addCaptain($user->getId());
        }

        $em->persist($team);
        $em->flush();
        return $this->json([
            'success' => true,
            'data' => ['captains' => $team->getCaptains()]
        ]);
    }
}
