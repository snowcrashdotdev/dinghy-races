<?php

namespace App\Controller;

use App\Entity\Draft;
use App\Entity\DraftEntry;
use App\Entity\TournamentUser;
use App\Repository\DraftRepository;
use App\Repository\TournamentUserRepository;
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
    public function invite_accept(Request $request, Draft $draft, TournamentUserRepository $tournamentUsers)
    {
        if ($this->isCsrfTokenValid('invite'.$draft->getId(), $request->request->get('_token'))) {
            $user = $tournamentUsers->findOneBy([
                'user' => $this->getUser(),
                'tournament' => $draft->getTournament()
            ]);

            if ($user) {
                $this->addFlash('error', 'You have already entered this tournament.');
            } else {
                $manager = $this->getDoctrine()->getManager();
                $user = new TournamentUser();
                $draft->getTournament()->addUser($user);
                $this->getUser()->addAppearance($user);
                $manager->persist($user);

                if ($draft->getTournament()->getFormat() === 'TEAM') {
                    $draftEntry = new DraftEntry();
                    $draftEntry->setUser($user);
                    $draft->addDraftEntry($draftEntry);
                    $manager->persist($draftEntry);
                }
                $this->addFlash('success', 'You entered the draft!');
                $manager->flush();
            }
        } else {
            $this->addFlash('error', 'Invalid request.');
        }

        return $this->redirectToRoute('tournament_show', ['id' => $draft->getTournament()->getId()]);
    }

    /**
     * @Route("/invite/decline/{id}", name="invite_decline", methods={"DELETE"})
     */
    public function invite_decline(Request $request, Draft $draft, TournamentUserRepository $tournamentUsers)
    {
        if ($this->isCsrfTokenValid('invite'.$draft->getId(), $request->request->get('_token'))) {
            $user = $tournamentUsers->findOneBy([
                'user' => $this->getUser(),
                'tournament' => $draft->getTournament()
            ]);

            if ($user) {
                $manager = $this->getDoctrine()->getManager();
                $manager->remove($user);
                $manager->flush();
                $this->addFlash('success', 'You have withdrawn from the tournament.');
            } else {
                $this->addFlash('error', 'You have not signed up for this tournament.');
            }
        } else {
            $this->addFlash('error', 'Invalid request.');
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

            $this->addFlash('success', 'Invite URL updated.');
        }

        return $this->redirectToRoute('draft_show', [
            'id' => $draft->getId()
        ]);
    }

    /**
     * @Route("/entry/{user}", name="entry_remove", methods={"DELETE"})
     * @Entity("user", expr="repository.find(user)")
     * @Security("is_granted('ROLE_TO')")
     */
    public function removeEntry(Request $request, TournamentUser $user)
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
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
