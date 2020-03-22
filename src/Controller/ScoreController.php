<?php

namespace App\Controller;

use App\Service\ImageUploader;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use App\Entity\Score;
use App\Entity\Game;
use App\Entity\Tournament;
use App\Form\ScoreType;
use App\Repository\ScoreRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * @Route("/scores")
 */
class ScoreController extends AbstractController
{
    /**
     * @Route("/new/{game_name}/{tournament_id}", name="score_new", methods={"GET","POST"})
     * @Entity("tournament", expr="repository.find(tournament_id)")
     * @ParamConverter("game", options={"mapping": {"game_name": "name"}})
     */
    public function new(Request $request, ScoreRepository $scoreRepo, Game $game, Tournament $tournament): Response
    {
        $user = $this->getUser();

        if (empty($user)) {
            $this->redirectToRoute('app_login');
        }

        $score = $scoreRepo->findOneBy([    
            'user' => $user,
            'tournament' => $tournament,
            'game' => $game
        ]);

        if (empty($score)) {
            $score = new Score();
            $score->setUser($user);
            $score->setGame($game);
            $score->setTournament($tournament);
            $score->setTeam($user->getTeam($tournament));
            $score->setAutoAssigned(false);
            $this->getDoctrine()->getManager()->persist($score);
        }

        $upload_dir = $this->getParameter('screenshot_dir');

        try {
            $score->setScreenshot(
                new File($upload_dir . '/' . $score->getScreenshot())
            );
        } catch (FileException $e) {
            $score->setScreenshot(null);
        }

        $form = $this->createForm(ScoreType::class, $score);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $screenshot_file = $form['screenshot']->getData();

            if ($screenshot_file) {
                $uploader = new ImageUploader($upload_dir);
                $score->setScreenshot(
                    $uploader->upload($screenshot_file)
                );
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('tournament_scores',
                [
                    'id'=>$score->getTournament()->getId(),
                    'game'=>$score->getGame()->getId()
                ]
            );
        }

        return $this->render('score/new.html.twig', [
            'score' => $score,
            'form' => $form->createView(),
        ]);
    }
}
