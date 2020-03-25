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
     * @Route("/{game_name}/{tournament_id}/new", name="score_new", methods={"GET","POST"})
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
            $old_screenshot = $score->getScreenshot();
            $new_screenshot_file = $form['screenshot']->getData();

            if ($new_screenshot_file) {
                $uploader = new ImageUploader($upload_dir);
                $score->setScreenshot(
                    $uploader->upload($new_screenshot_file)
                );
            } elseif (method_exists($old_screenshot, 'getFilename')) {
                $score->setScreenshot($old_screenshot->getFilename());
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('score_show',
                [
                    'tournament_id'=>$score->getTournament()->getId(),
                    'game_name'=>$score->getGame()->getName()
                ]
            );
        }

        return $this->render('score/new.html.twig', [
            'score' => $score,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{game_name}/{tournament_id}", name="score_show")
     * @Entity("tournament", expr="repository.find(tournament_id)")
     * @ParamConverter("game", options={"mapping": {"game_name": "name"}})
     */
    public function show(Tournament $tournament, Game $game, ScoreRepository $repo)
    {
        $scores = $repo->findBy([
            'game' => $game,
            'tournament' => $tournament
        ], ['points' => 'DESC']);

        $teams = $repo->findTeamScores($tournament, $game);

        $user_score = null;
        $user = $this->getUser();
        if (!empty($user)) {
            $user_score = $repo->findOneBy([
                'game' => $game,
                'tournament' => $tournament,
                'user' => $user
            ]);
        }

        return $this->render('score/show.html.twig', [
            'scores' => $scores,
            'game' => $game,
            'tournament' => $tournament,
            'teams' => $teams,
            'user_score' => $user_score
        ]);
    }
}
