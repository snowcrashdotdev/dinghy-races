<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/sign-up", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $entityManager = $this->getDoctrine()->getManager();
            $user->setResetToken(bin2hex(random_bytes(32)));
            $entityManager->persist($user);
            $entityManager->flush();

            $verify_url = $this->generateUrl('app_verify', [
                'user'=> $user->getId(),
                'token'=>$user->getResetToken(),
                ], UrlGeneratorInterface::ABSOLUTE_URL
            );

            $message = (new \Swift_Message('Please verify your email address'))
                ->setFrom($this->getParameter('default_sender'))
                ->setTo($user->getEmail())
                ->setBody($this->renderView('emails/registration.html.twig', [
                    'verify_url' => $verify_url
                ]), 'text/html'
            );
            
            $mailer->send($message);

            return $this->redirectToRoute('app_welcome');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        $scores = $this->getDoctrine()
            ->getRepository('App\Entity\Score')
            ->findUserScores($user, 15);

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'scores' => $scores
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/search/{name}", name="user_search", defaults={"name"=""}, methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_TO')")
     */
    public function search(Request $request, string $name, UserRepository $userRepository): Response
    {
        return $this->json([
            'data'=> $userRepository->searchBySubstring($name)
         ]);
    }

    /**
     * @Route("/{user}/personal-bests", name="user_pbs")
     */
    public function personal_bests(Request $request, User $user)
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