<?php

namespace App\Controller;

use App\Entity\PersonalBest;
use App\Entity\Game;
use App\Form\ScoreType;
use App\Repository\PersonalBestRepository;
use App\Service\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
    public function new(Request $request, String $game, PersonalBestRepository $personalBestRepository): Response
    {
        $upload_dir = $this->getParameter('screenshot_dir');
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

        try {
            $personalBest->setScreenshotFile(
                new File($upload_dir . '/' . $personalBest->getScreenshot())
            );
        } catch (FileException $e) {
            $personalBest->setScreenshotFile(null);
        }

        $form = $this->createForm(ScoreType::class, $personalBest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $screenshot_file = $form->get('screenshot_file')->getData();
            $screenshot_remove = intval(
                $form->get('screenshot_file_remove')->getData()
            );

            if ($screenshot_file) {
                $uploader = new ImageUploader($upload_dir);
                $personalBest->setScreenshot(
                    $uploader->upload($screenshot_file)
                );
            } elseif ($screenshot_remove === 1) {
                $personalBest->setScreenshot(null);
            }

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
