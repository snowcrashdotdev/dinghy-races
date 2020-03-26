<?php

namespace App\Controller;

use App\Form\UserProfileType;
use App\Service\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProfileController extends AbstractController
{
    /**
     * @Route("/settings", name="profile_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request): Response
    {
        $profile = $this->getUser()->getProfile();
        $upload_dir = $this->getParameter('pfp_dir');

        $form = $this->createForm(UserProfileType::class, $profile);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture_file = $form->get('picture_file')->getData();

            if ($picture_file) {
                $uploader = new ImageUploader($upload_dir);
                $profile->setPicture(
                    $uploader->upload($picture_file)
                );
            }
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->render('profile/edit.html.twig', [
            'profile' => $profile,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/users/{username}", name="profile_show")
     */
    public function show(String $username)
    {
        $user = $this->getDoctrine()
            ->getRepository('App\Entity\User')
            ->findOneBy([
                'username' => $username
            ])
        ;

        if (empty($user)) {
            return $this->redirectToRoute('dashboard');
        } else {
            $profile = $user->getProfile();
            return $this->render('profile/show.html.twig', [
                'user' => $user,
                'profile' => $profile
            ]);
        }
    }
}