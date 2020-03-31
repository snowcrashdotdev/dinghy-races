<?php

namespace App\Controller;

use App\Entity\Draft;
use App\Entity\DraftEntry;
use App\Repository\DraftRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\Request;

class DraftController extends AbstractController
{
    /**
     * @Route("/invite/{token}", name="draft_invite", methods={"GET"})
     * @Entity("tournament", expr="repository.findBy(invite_token = token)")
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
        if (
            $this->isCsrfTokenValid('invite'.$draft->getId(), $request->request->get('_token')) &&

            ! $draft->hasEntered($this->getUser())
        
        ) {
            $draftEntry = new DraftEntry();
            $this->getUser()->addDraftEntry($draftEntry);
            $draft->addDraftEntry($draftEntry);
            
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'You entered the draft!');
        } else {
            $this->addFlash('error', 'You were unable to enter.');
        }

        return $this->redirectToRoute('tournament_show', ['id' => $draft->getTournament()->getId()]);
    }

    /**
     * @Route("/invite/decline/{id}", name="invite_decline", methods={"DELETE"})
     */
    public function invite_decline(Request $request, Draft $draft)
    {
        if ($this->isCsrfTokenValid('invite'.$draft->getId(), $request->request->get('_token'))) {
            $draftEntry = $this->getDoctrine()
                ->getRepository('App\Entity\DraftEntry')
                ->findOneBy(['user' => $this->getUser()]);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($draftEntry);
            $entityManager->flush();
            $this->addFlash('notice', 'You withdew from the tournament.');
        }

        return $this->redirectToRoute('tournament_show', ['id' => $draft->getTournament()->getId()]);
    }

    /**
     * @Route("/drafts", name="draft_index", methods={"GET"})
     */
    public function index(DraftRepository $draftRepository)
    {
        return $this->render('draft/index.html.twig', [
            'drafts' => $draftRepository->findAll(),
        ]);
    }

    /**
     * @Route("/drafts/{id}", name="draft_show", methods={"GET"})
     */
    public function show(Draft $draft)
    {
        return $this->render('draft/show.html.twig', [
            'draft' => $draft
        ]);
    }
}
