<?php

namespace App\Controller;

use App\Entity\Profile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProfileController extends AbstractController
{
    /**
     * @Route("/settings/profile", name="settings_profile", methods={"GET", "POST"})
     * @IsGranted("ROLE_USER")
     */
    public function edit(Request $request): Response
    {
        $profile = $this->getUser()->getProfile();

        $form = $this->createFormBuilder($profile)
            ->add('social', TextType::class, [
                'label' => 'Streaming URL: ',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://twitch.tv/1ccmarathon',
                    'class' => 'score-input'
                ]
            ])
            ->add('save', SubmitType::class)
            ->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->render('profile/edit.html.twig', [
            'profile' => $profile,
            'form' => $form->createView()
        ]);
    }
}