<?php

namespace App\Controller;

use App\Entity\Draft;
use App\Repository\DraftRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class DraftController extends AbstractController
{
    /**
     * @Route("/invite/{token}", name="draft_invite", methods={"GET"})
     */
    public function invite(String $token, DraftRepository $draftRepository)
    {
        $draft = $draftRepository->findOneBy(['invite_token' => $token]);

        return $this->render('draft/invite.html.twig', ['draft' => $draft]);
    }

    /**
     * @Route("/invite/accept/{id}", name="invite_accept", methods={"POST"})
     */
    public function invite_accept(Request $request, Draft $draft)
    {
        if ($this->isCsrfTokenValid('invite'.$draft->getId(), $request->request->get('_token'))) {
            $draft->addEntry($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($draft);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tournament_show', ['id' => $draft->getTournament()->getId()]);
    }

    /**
     * @Route("/invite/decline/{id}", name="invite_decline", methods={"DELETE"})
     */
    public function invite_decline(Request $request, Draft $draft)
    {
        if ($this->isCsrfTokenValid('invite'.$draft->getId(), $request->request->get('_token'))) {
            $draft->removeEntry($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($draft);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tournament_show', ['id' => $draft->getTournament()->getId()]);
    }
}
