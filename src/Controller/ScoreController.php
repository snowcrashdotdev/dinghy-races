<?php

namespace App\Controller;

use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Score;
use App\Entity\Game;
use App\Entity\Tournament;
use App\Form\ScoreType;
use App\Service\ScoreKeeper;

/**
 * @Route("/score")
 */
class ScoreController extends AbstractController
{
    /**
     * @Route("/new/{game}/{tournament}", name="score_new", methods={"GET","POST"})
     */
    public function new(Request $request, Game $game, Tournament $tournament): Response
    {
        if (
            ! $tournament->getUsers()->contains($this->getUser())
        ) {
            return $this->redirectToRoute('tournament_scores',
                [
                    'id'=> $tournament->getId(),
                    'game'=>$game->getId()
                ]
            );
        }
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
        }

        if ($tournament->isAfterCutoff()) {
            $this->addFlash('notice', 'It is too late to add a score for this game.');
            return $this->redirectToRoute('tournament_scores',
                [
                    'id'=> $tournament->getId(),
                    'game'=>$game->getId()
                ]
            );
        }

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
            
            $scorekeeper = new ScoreKeeper($tournament, $entityManager);
            $scorekeeper->scoreGame($game);
            $scorekeeper->scoreTeams();

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

    /**
     * @Route("/{id}/edit", name="score_edit", methods={"GET","PATCH"})
     */
    public function edit(Request $request, Score $score, FileUploader $uploader): Response
    {
        if (
            $this->getUser() !== $score->getUser()
        ) {
            return $this->redirectToRoute('tournament_scores',
                [
                    'id'=>$score->getTournament()->getId(),
                    'game'=>$score->getGame()->getId()
                ]
            );
        }

        if ($screenshot = $score->getScreenshot() ) {
            $path = $uploader->getTargetDirectory() . '/' . $screenshot;
            if (file_exists($path)) {
                $score->setScreenshot(new File($path));
            } else {
                $score->setScreenshot(null);
            }
        }
        $oldScore = clone $score;

        $form = $this->createForm(ScoreType::class, $score, [
            'method' => 'PATCH',
            'empty_data' => [
                'screenshot' => $screenshot
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newScore = $form->getData();

            if ($oldScore->getPoints() !== $newScore->getPoints()) {
                $score->setDateUpdated(new \DateTime('now'));
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $scorekeeper = new ScoreKeeper($score->getTournament(), $entityManager);
            $scorekeeper->scoreGame($score->getGame());
            $scorekeeper->scoreTeams();

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
