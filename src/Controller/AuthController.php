<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
     * @Route("/lost-password", name="app_lost_password", methods={"GET", "POST"})
     */
    public function lostPassword(Request $request, \Swift_Mailer $mailer)
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'Your email address.',
                ]
            ])
            ->add('save', SubmitType::class, ['label' => 'Send'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()['email'];
            $user = $this->getDoctrine()->getRepository('App\Entity\User')
                ->findOneBy(['email' => $email]);

            if ($user) {
                $entityManager = $this->getDoctrine()->getManager();
                $user->setResetToken(bin2hex(random_bytes(32)));
                $entityManager->persist($user);
                $entityManager->flush();

                $verify_url = $this->generateUrl('app_verify', [
                    'user'=> $user->getId(),
                    'token'=>$user->getResetToken(),
                    'lost-password'=> true
                    ], UrlGeneratorInterface::ABSOLUTE_URL
                );

                $message = (new \Swift_Message('Password reset request'))
                    ->setFrom($this->getParameter('default_sender'))
                    ->setTo($email)
                    ->setBody($this->renderView('_emails/password-reset.html.twig',
                        [
                            'verify_url' => $verify_url
                        ]
                    ), 'text/html');
                
                $mailer->send($message);
            }
            
            $this->addFlash(
                'notice',
                'Check your email.'
            );

            return $this->redirectToRoute('index');
        }
     

        return $this->render('security/lost-password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/verify/{user}/{token}", name="app_verify", methods={"GET", "PATCH"})
     */
    public function verify(Request $request, User $user, string $token, UserPasswordEncoderInterface $encoder)
    {
        if ($user->getResetToken() !== $token) {
            $this->addFlash(
                'error',
                'Invalid security token.'
            );
            return $this->redirectToRoute('tournament_index');
        }

        $em = $this->getDoctrine()->getManager();

        if ($request->query->get('lost-password')) {
            $form = $this->createForm(UserType::class, $user, [
                'method' => 'PATCH',
                'attr' => ['class' => 'bg-white margin-y padding text-align-center']
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $password = $encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($password);
                $user->setResetToken(null);
                $em->persist($user);
                $em->flush();

                $this->addFlash(
                    'notice',
                    'Password changed.'
                );

                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/password-reset.html.twig', [
                'user' => $user,
                'form' => $form->createView()
            ]);

        } else {
            $user->setVerified(true)->setResetToken(null);
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'Account verified!'
            );

            return $this->redirectToRoute('app_login');
        }
    }
}