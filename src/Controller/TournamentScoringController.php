<?php

namespace App\Controller;

use App\Entity\TournamentScoring;
use App\Entity\Tournament;
use App\Form\TournamentScoringType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Repository\TournamentScoringRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

/**
 * @Route("/tournament/scoring")
 * @IsGranted("ROLE_TO")
 */
class TournamentScoringController extends AbstractController
{
    /**
     * @Route("/", name="scoring_index", methods={"GET"})
     */
    public function index(TournamentScoringRepository $tournamentScoringRepository): Response
    {
        return $this->render('tournament_scoring/index.html.twig', [
            'tournament_scorings' => $tournamentScoringRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="scoring_show", methods={"GET"})
     */
    public function show(TournamentScoring $tournamentScoring): Response
    {
        return $this->render('tournament_scoring/show.html.twig', [
            'tournament_scoring' => $tournamentScoring,
        ]);
    }

    /**
     * @Route("/{tournament}/edit", name="scoring_edit", methods={"GET","POST"})
     * @Entity("tournament", expr="repository.find(tournament)")
     */
    public function edit(Request $request, Tournament $tournament): Response
    {
        if (empty(
            $tournamentScoring = $tournament->getScoring()
        )) {
            $tournamentScoring = new TournamentScoring();
            $tournament->setScoring($tournamentScoring);
            $this->getDoctrine()->getManager()->flush();
        }

        $form = $this->createForm(TournamentScoringType::class, $tournamentScoring);
        $tableForm = $this->createScoringTableForm($tournament);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
        }

        $tableForm->handleRequest($request);
        if ($tableForm->isSubmitted() && $tableForm->isValid()) {
            $pointsTable = $tableForm->getData();
            $tournamentScoring->setPointsTable($pointsTable);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->render('tournament_scoring/edit.html.twig', [
            'tournament' => $tournament,
            'tournament_scoring' => $tournamentScoring,
            'table_form' => $tableForm->createView(),
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

    private function createScoringTableForm(Tournament $tournament)
    {
        $forms = $this->get('form.factory');
        $count = $tournament->getUsers()->count();
        $form = $forms->createNamedBuilder('scoring_table', FormType::class, null, [
            'attr' => [ 'class' => 'ajax-form' ]
        ]);
        $place = 1;
        $table = $tournament->getScoring()->getPointsTable();
        while ($place <= $count) {
            if ( isset( $table[$place] ) ) {
                $options = ['data' => $table[$place]];
            } else {
                $options = [];
            }
            $form = $form->add($place, IntegerType::class, $options);
            $place++;
        }

        return $form->getForm();
    }
}
