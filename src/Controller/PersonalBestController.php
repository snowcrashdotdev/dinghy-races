<?php

namespace App\Controller;

use App\Entity\PersonalBest;
use App\Form\PersonalBestType;
use App\Repository\PersonalBestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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
     */
    public function show(String $game): Response
    {
        $game = $this->getDoctrine()
            ->getRepository('App\Entity\Game')
            ->findOneBy(['name' => $game
        ]);

        return $this->render('personal_best/show.html.twig', [
            'game' => $game,
        ]);
    }

    /**
     * @Route("/{game}/new", name="pb_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Request $request, String $game, PersonalBestRepository $personalBestRepository): Response
    {
        $game = $this->getDoctrine()
            ->getRepository('App\Entity\Game')
            ->findOneBy(['name' => $game])
        ;

        if (empty(
                $personalBest = $personalBestRepository->findOneBy([
                    'game' => $game,
                    'user' => $this->getUser()
                ])
            )
        ) {
            $personalBest = new PersonalBest();
            $personalBest->setGame($game);
            $personalBest->setUser($this->getUser());
            $this->getDoctrine()->getManager()->persist($personalBest);
        }

        $form = $this->createForm(PersonalBestType::class, $personalBest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('pb_index');
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
