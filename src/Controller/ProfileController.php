<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserProfileType;
use App\Service\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

class ProfileController extends AbstractController
{
    /**
     * @Route("/settings", name="profile_edit", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function edit(Request $request): Response
    {
        $profile = $this->getUser()->getProfile();

        $form = $this->createForm(UserProfileType::class, $profile);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('profile_show', [
                'username' => $this->getUser()->getUsername()
            ]);
        }

        return $this->render('profile/edit.html.twig', [
            'profile' => $profile,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/users/{username}", name="profile_show")
     * @Entity("user", expr="repository.findOneBy({username: username})")
     */
    public function show(User $user)
    {
        $profile = $user->getProfile();
        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'profile' => $profile
        ]);
    }


    /**
     * @Route("/{user}/personal-bests", name="user_pbs")
     * @Entity("user", expr="repository.findOneBy({username: user})")
     */
    public function personal_bests(User $user)
    {
        $pbs = $this->getDoctrine()
            ->getRepository('App\Entity\PersonalBest')
            ->findBy([
                'user' => $user
            ])
        ;

        return $this->render('user/personal_bests.html.twig', [
            'user' => $user,
            'personal_bests' => $pbs
        ]);
    }
}