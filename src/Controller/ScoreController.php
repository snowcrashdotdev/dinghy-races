<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use App\Entity\Score;
use App\Form\ScoreType;
use App\Event\NewScoreEvent;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/score")
 */
class ScoreController extends AbstractController
{
    /**
     * @Route("/new/{game}/{tournament}", name="score_new", methods={"GET","POST"})
     */
    public function new(Request $request, $game, $tournament, EventDispatcherInterface $dispatcher): Response
    {
        if (
            $score = $this->getDoctrine()
                ->getRepository('App\Entity\Score')
                ->findOneBy([
                    'tournament' => $tournament,
                    'game' => $game,
                    'user' => $this->getUser()->getId()
                ])
        ) {
            return $this->redirectToRoute('score_edit', [
                'id' => $score->getId()
            ]);
        } else {
            $user = $this->getUser();

            $game = $this->getDoctrine()
                ->getRepository('App\Entity\Game')
                ->find($game);

            $tournament = $this->getDoctrine()
                ->getRepository('App\Entity\Tournament')
                ->find($tournament);
            
            $team = $tournament->getTeamByUser($user);
            $score = new Score($game, $tournament, $user, $team);
            $form = $this->createForm(ScoreType::class, $score);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $score->setDateUpdated(new \DateTime('now'));
                $entityManager->persist($score);
                $entityManager->flush();

                $event = new NewScoreEvent($score);
                $dispatcher->dispatch(NewScoreEvent::NAME, $event);

                return $this->redirectToRoute('tournament_scores',
                    [
                        'id'=>$score->getTournament()->getId(),
                        'game'=>$score->getGame()->getId()
                    ]
                );
            }

            return $this->render('score/new.html.twig', [
                'score' => $score,
                'form' => $form->createView()
            ]);
        }
    }

    /**
     * @Route("/{id}/edit", name="score_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Score $score, EventDispatcherInterface $dispatcher): Response
    {
        $form = $this->createForm(ScoreType::class, $score);
        $form->handleRequest($request);
        $score->setDateUpdated(new \DateTime('now'));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $event = new NewScoreEvent($score);
            $dispatcher->dispatch(NewScoreEvent::NAME, $event);

            return $this->redirectToRoute('tournament_scores',
                [
                    'id'=>$score->getTournament()->getId(),
                    'game'=>$score->getGame()->getId()
                ]
            );
        }

        return $this->render('score/edit.html.twig', [
            'score' => $score,
            'form' => $form->createView(),
        ]);
    }
}
