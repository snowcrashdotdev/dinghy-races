<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Entity\User;
use App\Repository\TournamentScoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function stream_kit(Request $request, Tournament $tournament, User $user, TournamentScoreRepository $repo, SerializerInterface $serializer)
    {
        if ($request->isXmlHttpRequest()) {
            $scores = $repo->findBy([
                'user' => $user,
                'tournament' => $tournament,
                'auto_assigned' => 0,
            ], ['team_points' => 'DESC']);

            $team_scores = $repo->findTeamScores($tournament);

            $top_player = $repo->findIndividualScores($tournament, 1);

            $stats = $repo->findTournamentResults($tournament, null, $user);
            unset($stats[0]);

            $data = $serializer->serialize(
                [
                    'stats' => $stats,
                    'scores' => $scores,
                    'teamScores' => $team_scores,
                    'topPlayer' => $top_player
                ],
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
}
