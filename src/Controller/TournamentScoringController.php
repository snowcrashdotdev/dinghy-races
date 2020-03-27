<?php

namespace App\Controller;

use App\Entity\TournamentScoring;
use App\Form\TournamentScoringType;
use App\Repository\TournamentScoringRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/tournament/scoring")
 * @IsGranted("ROLE_TO")
 */
class TournamentScoringController extends AbstractController
{
    /**
     * @Route("/", name="tournament_scoring_index", methods={"GET"})
     */
    public function index(TournamentScoringRepository $tournamentScoringRepository): Response
    {
        return $this->render('tournament_scoring/index.html.twig', [
            'tournament_scorings' => $tournamentScoringRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="tournament_scoring_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $tournamentScoring = new TournamentScoring();
        $form = $this->createForm(TournamentScoringType::class, $tournamentScoring);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($tournamentScoring);
            $entityManager->flush();

            return $this->redirectToRoute('tournament_scoring_index');
        }

        return $this->render('tournament_scoring/new.html.twig', [
            'tournament_scoring' => $tournamentScoring,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tournament_scoring_show", methods={"GET"})
     */
    public function show(TournamentScoring $tournamentScoring): Response
    {
        return $this->render('tournament_scoring/show.html.twig', [
            'tournament_scoring' => $tournamentScoring,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tournament_scoring_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, TournamentScoring $tournamentScoring): Response
    {
        $form = $this->createForm(TournamentScoringType::class, $tournamentScoring);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tournament_scoring_index');
        }

        return $this->render('tournament_scoring/edit.html.twig', [
            'tournament_scoring' => $tournamentScoring,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="tournament_scoring_delete", methods={"DELETE"})
     */
    public function delete(Request $request, TournamentScoring $tournamentScoring): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tournamentScoring->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($tournamentScoring);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tournament_scoring_index');
    }
}
