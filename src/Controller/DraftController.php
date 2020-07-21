<?php

namespace App\Controller;

use App\Entity\Draft;
use App\Entity\DraftEntry;
use App\Repository\DraftRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DraftController extends AbstractController
{
    /**
     * @Route("/invite/{token}", name="draft_invite", methods={"GET"})
     * @Entity("draft", expr="repository.findOneBy({invite_token: token})")
     */
    public function invite(Draft $draft)
    {
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
            if ($draft->getTournament()->getFormat() === 'TEAM') {
                $draftEntry = new DraftEntry();
                $this->getUser()->addDraftEntry($draftEntry);
                $draft->addDraftEntry($draftEntry);
            } else {
                $draft->getTournament()->addUser($this->getUser());
            }
            $this->addFlash('success', 'You entered the draft!');
            $this->getDoctrine()->getManager()->flush();
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
            if ($draft->getTournament()->getFormat() === 'TEAM') {
                $draftEntry = $this->getDoctrine()
                    ->getRepository('App\Entity\DraftEntry')
                    ->findOneBy(['user' => $this->getUser()]);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($draftEntry);
            } else {
                $draft->getTournament()->removeUser($this->getUser());
            }
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

    /**
     * @Route("/drafts/{id}/edit", name="draft_edit", methods={"POST"})
     * @Security("is_granted('ROLE_TO')")
     */
    public function edit(Draft $draft, Request $request)
    {
        if ($this->isCsrfTokenValid('edit'.$draft->getId(), $request->request->get('_token'))) {
            $manager = $this->getDoctrine()->getManager();
            $inviteToken = urlencode($request->request->get('invite_token'));
            $draft->setInviteToken($inviteToken);
            $manager->flush();
        }
        return $this->redirectToRoute('draft_show', [
            'id' => $draft->getId()
        ]);
    }
    /**
     * @Route("/entry/{draft_entry}", name="entry_remove", methods={"DELETE"})
     * @Entity("draftEntry", expr="repository.find(draft_entry)")
     * @Security("is_granted('ROLE_TO')")
     */
    public function removeEntry(Request $request, DraftEntry $draftEntry)
    {
        $draft = $draftEntry->getDraft();

        if ($this->isCsrfTokenValid('delete'.$draftEntry->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($draftEntry);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'message' => 'Entry removed from draft.'
                ]);
            }
        }

        return $this->redirectToRoute('draft_show', [
                'id' => $draft->getId()
            ]
        );
    }
}
