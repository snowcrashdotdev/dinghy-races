<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
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
}
