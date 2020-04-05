<?php

namespace App\Controller;

use App\Entity\TournamentScoring;
use App\Entity\Tournament;
use App\Service\ScoreKeeper;
use App\Form\TournamentScoringType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
     * @Route("/{tournament}/refresh", name="scoring_refresh", methods={"POST"})
     * @Entity("tournament", expr="repository.find(tournament)")
     */
    public function refresh(Request $request, Tournament $tournament, ScoreKeeper $score_keeper)
    {
        if ($this->isCsrfTokenValid('refresh'.$tournament->getScoring()->getId(), $request->request->get('_token'))) {
            if ($request->isXmlHttpRequest()) {
                $score_keeper->scoreTournament($tournament);
                return $this->json([
                    'success' => true,
                    'message' => "${tournament} has been re-scored!"
                ]);
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => false,
                'message' => "${tournament} could not be scored."
            ]);
        }

        return $this->redirectToRoute('tournament_show', [
            'id' => $tournament->getId()
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

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                'success',
                'Your changes were saved!'
            );
        }

        $tableByText = $this->createScoringTableTextForm();
        $tableByText->handleRequest($request);
        if ($tableByText->isSubmitted() && $tableByText->isValid()) {
            $list = $tableByText->get('points_list')->getData();
            $table = array_map(
                'intval',
                explode(',', preg_replace('/\s+/', '', trim($list)))
            );

            if (rsort($table)) {
                array_unshift($table, null);
                unset($table[0]);
                $tournamentScoring->setPointsTable($table);
                $this->addFlash('success', 'Points table updated!');
                $this->getDoctrine()->getManager()->flush();
                $this->redirectToRoute('scoring_edit', [
                    'tournament' => $tournament->getId()
                ]);
            }
        }

        $tableForm = $this->createScoringTableForm($tournament);
        $tableForm->handleRequest($request);
        if ($tableForm->isSubmitted() && $tableForm->isValid()) {
            $pointsTable = $tableForm->getData();
            $tournamentScoring->setPointsTable($pointsTable);
            $this->getDoctrine()->getManager()->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'message' => 'Tournament points table updated!'
                ]);
            } else {
                $this->addFlash('success', 'Tournament points table updated!');
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => false,
                'message' => 'Could not save tournament points table.'
            ]);
        }

        return $this->render('tournament_scoring/edit.html.twig', [
            'tournament' => $tournament,
            'tournament_scoring' => $tournamentScoring,
            'table_form' => $tableForm->createView(),
            'form' => $form->createView(),
            'table_by_text_form' => $tableByText->createView()
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

        return $this->redirectToRoute('tournament_index');
    }

    private function createScoringTableForm(Tournament $tournament)
    {
        $forms = $this->get('form.factory');
        $count = count($tournament->getScoring()->getPointsTable());
        $form = $forms->createNamedBuilder('scoring_table', FormType::class, null, [
            'attr' => [ 'class' => 'ajax-form' ]
        ]);
        $place = 1;
        $table = $tournament->getScoring()->getPointsTable();
        while ($place <= $count) {
            $options = ['required' => false];
            if ( isset( $table[$place] ) ) {
                $options = ['data' => $table[$place]];
            }
            $form = $form->add($place, IntegerType::class, $options);
            $place++;
        }

        return $form->getForm();
    }

    private function createScoringTableTextForm()
    {
        return $this->get('form.factory')
            ->createNamedBuilder('scoring_table_text', FormType::class) 
            ->add('points_list', TextareaType::class, [
                'label' => 'List',
                'attr' => ['placeholder' => '300,290,290...']
            ])
            ->getForm()
        ;
    }
}