<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\Tournament;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/streamkit")
 */

class StreamKitController extends AbstractController
{
    /**
     * @Route("/{username}/{tournament}/scores", name="stream_kit", methods={"GET"})
     */
    public function scores(String $username, Tournament $tournament, UserRepository $users) : Response
    {
        $params = null;
        $user = $users->findOneByUsername($username);
        if ($user && $tournament) {
            $params = [
                'user' => $user->getId(),
                'tournament' => $tournament->getId()
            ];
        }
        return $this->render('stream_kit/scores.html.twig', [
            'params' => $params
        ]);
    }
}
