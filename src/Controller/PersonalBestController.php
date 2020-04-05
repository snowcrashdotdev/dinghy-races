<?php

namespace App\Controller;

use App\Entity\PersonalBest;
use App\Entity\Game;
use App\Form\ScoreType;
use App\Repository\PersonalBestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/permanent-boards")
 */
class PersonalBestController extends AbstractController
{
    /**
     * @Route("/", name="pb_index", methods={"GET"})
     */
    public function index(): Response
    {
        $games = $this->getDoctrine()
            ->getRepository('App\Entity\Game')
            ->findAllHavingScores()
        ;

        return $this->render('personal_best/index.html.twig', [
            'games' => $games,
        ]);
    }

    /**
     * @Route("/{game}", name="pb_show", methods={"GET"})
     * @ParamConverter("game", options={"mapping": {"game": "name"}})
     */
    public function show(Game $game, PersonalBestRepository $pbRepo): Response
    {
        $personalBests = $pbRepo->findBy([
            'game' => $game
        ]);

        return $this->render('personal_best/show.html.twig', [
            'personal_bests' => $personalBests,
            'game' => $game,
        ]);
    }

    /**
     * @Route("/{game}/new", name="pb_new", methods={"GET","POST"})
     * @ParamConverter("game", options={"mapping": {"game": "name"}})
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Request $request, Game $game, PersonalBestRepository $personalBestRepository): Response
    {
        if (empty(
                $personalBest = $personalBestRepository->findOneBy([
                    'game' => $game,
                    'user' => $this->getUser()
                ])
            )
        ) {
            $this->addFlash('notice', 'Unable to find your previous score.');
            return $this->redirectToRoute('pb_show', [
                'game' => $game->getName()
            ]);
        }

        $form = $this->createForm(ScoreType::class, $personalBest);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Your score has been saved!');
            return $this->redirectToRoute('pb_show', [
                'game' => $game->getname()
            ]);
        }

        return $this->render('personal_best/new.html.twig', [
            'personal_best' => $personalBest,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="personal_best_delete", methods={"DELETE"})
     */
    public function delete(Request $request, PersonalBest $personalBest): Response
    {
        if ($this->isCsrfTokenValid('delete'.$personalBest->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($personalBest);
            $entityManager->flush();
        }

        return $this->redirectToRoute('personal_best_index');
    }
}
