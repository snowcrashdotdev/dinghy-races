<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function index()
    {
        $user = $this->getUser();

        if (empty($user)) {
            return $this->redirectToRoute('tournament_index');
        }

        return $this->render('dashboard/index.html.twig', [
            'user' => $user
        ]);
    }
}
