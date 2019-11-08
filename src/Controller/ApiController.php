<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use App\Entity\User;
use App\Entity\Tournament;
use App\Repository\TournamentRepository;
use App\Repository\UserRepository;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/scores", name="scores_api", methods={"GET"})
     */
    public function scores(Request $request, TournamentRepository $tournamentRepository, UserRepository $userRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $user_id = $request->query->get('user');
            $tournament_id = $request->query->get('tournament');
            $tournament = $tournamentRepository->findOneBy(['id' => $tournament_id]);
            $user = $userRepository->findOneBy(['id' => $user_id]);

            $expr = new Comparison('user', '=', $user);
            $criteria = new Criteria();
            $criteria->where($expr);
            $scores = $tournament->getScores()->matching($criteria);

            $data = array('result' => array());
            if ($scores) {
                foreach($scores as $score) {
                    $data['result'][] = array(
                        'game' => $score->getGame()->getName(),
                        'points' => $score->getPoints()
                    );
                }
            }
            return $this->json($data);
        } else {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->send();
        }
    }
}
