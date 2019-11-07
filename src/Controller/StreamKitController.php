<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\Tournament;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/streamkit")
 */

class StreamKitController extends AbstractController
{
    /**
     * @Route("/{username}/{tournament}/scores", name="stream_kit")
     */
    public function scores(String $username, Tournament $tournament, UserRepository $users) : Response
    {
        $scores = null;
        $user = $users->findOneByUsername($username);

        if ($user) {
            $expr = new Comparison('user', '=', $user);
            $criteria = new Criteria();
            $criteria->where($expr);
            $scores = $tournament->getScores()->matching($criteria);
        }
        return $this->render('stream_kit/scores.html.twig', [
            'scores' => $scores,
        ]);
    }
}
