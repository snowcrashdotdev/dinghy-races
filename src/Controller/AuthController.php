<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

     /**
     * @Route("/welcome", name="app_welcome", methods={"GET"})
     */
    public function welcome(): Response
    {
        return $this->render('security/welcome.html.twig');
    }

    /**
     * @Route("/verify/{user}/{token}", name="app_verify", methods={"GET"})
     */
    public function verify(Request $request, User $user, string $token) {
        $em = $this->getDoctrine()->getManager();
        $user_token = $user->getResetToken();
        if ($user_token === null) {
            // No token - invalid request
            $this->addFlash(
                'warning',
                'Invalid security token.'
            );
        }

        if ($user_token === $token) {
            // Tokens match
            $user->setVerified(true)->setResetToken(null);
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'notice',
                'Account verified!'
            );
        }

        return $this->redirectToRoute('app_login');
    }
}
