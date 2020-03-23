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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ProfileController extends AbstractController
{
    /**
     * @Route("/settings", name="profile_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
     */
    public function edit(Request $request): Response
    {
        $profile = $this->getUser()->getProfile();
        $form = $this->createForm(UserProfileType::class, $profile);
        $upload_dir = $this->getParameter('pfp_dir');

        try {
            $profile->setPicture(
                new File($upload_dir . '/' . $profile->getPicture())
            );
        } catch (FileException $e) {
            $profile->setPicture(null);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $picture_file = $form['picture']->getData();

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