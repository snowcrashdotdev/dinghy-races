<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Entity\User;
use App\Repository\TournamentScoreRepository;
use App\Repository\TournamentUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/ajax")
 */
class AjaxController extends AbstractController
{
    /**
     * @Route("/search/game/{query}")
     */
    public function search_game(Request $request, ?string $query, SerializerInterface $serializer)
    {
        if ($request->isXmlHttpRequest()) {
            $games = $this->getDoctrine()
                ->getRepository('App\Entity\Game')
                ->findByLike($query);

            $data = $serializer->serialize(
                $games,
                'json',
                ['groups' => 'public']
            );

            return $this->json($data);
        } else {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->send();
        }
    }

    /**
     * @Route("/stream/{tournament}/{user}", name="stream_kit")
     * @Entity("tournament", expr="repository.find(tournament)")
     * @Entity("user", expr="repository.findOneBy({username: user})")
     */
    public function stream_kit(Request $request, Tournament $tournament, User $user, TournamentScoreRepository $tournamentScores, TournamentUserRepository $tournamentUsers, SerializerInterface $serializer)
    {
        if ($request->isXmlHttpRequest()) {
            $tournamentUser = $tournamentUsers->findOneBy([
                'user' => $user,
                'tournament' => $tournament
            ]);

            $recentScores = $tournamentScores->findBy(
                [ 'tournament' => $tournament ],
                [ 'updated_at' => 'DESC' ],
                3
            );

            $place = $tournament->getUsers()->indexOf($tournamentUser) + 1;

            $data = [
                'user' => $tournamentUser,
                'scores' => $tournamentUser->getScores()->toArray(),
                'place' => $place,
                'recent_scores' => $recentScores
            ];

            return new JsonResponse($data);
        } else {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->send();
        }
    }
}
