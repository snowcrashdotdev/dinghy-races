<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameType;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Service\ImageUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/games")
 * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_TO')")
 */
class GameController extends AbstractController
{
    /**
     * @Route("/", name="game_index", methods={"GET"})
     */
    public function index(GameRepository $gameRepository): Response
    {
        return $this->render('game/index.html.twig', [
            'games' => $gameRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="game_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $marquee_file = $form['marquee']->getData();

            if ($marquee_file) {
                $uploader = new ImageUploader($upload_dir);
                $game->setMarquee(
                    $uploader->upload($marquee_file)
                );
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($game);
            $entityManager->flush();

            return $this->redirectToRoute('game_index');
        }

        return $this->render('game/new.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="game_show", methods={"GET"})
     */
    public function show(Game $game): Response
    {
        return $this->render('game/show.html.twig', [
            'game' => $game,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="game_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Game $game): Response
    {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        $upload_dir = $this->getParameter('marquee_dir');

        try {
            $game->setMarqueeFile(
                new File($upload_dir . '/' . $game->getMarquee())
            );
        } catch (FileException $e) {
            $game->setMarquee(null);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $marquee_file = $form->get('marquee_file')->getData();

            if ($marquee_file) {
                $uploader = new ImageUploader($upload_dir);
                $game->setMarquee(
                    $uploader->upload($marquee_file)
                );
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('game_show', [
                'id' => $game->getId(),
            ]);
        }

        return $this->render('game/edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="game_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Game $game): Response
    {
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($game);
            $entityManager->flush();
        }

        return $this->redirectToRoute('game_index');
    }
}
