<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Form\TournamentType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Game;
use App\Entity\Draft;
use App\Repository\TournamentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Service\TwitchChecker;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @Route("/tournaments")
 */
class TournamentController extends AbstractController
{
    /**
     * @Route("/", name="tournament_index", methods={"GET"})
     */
    public function index(TournamentRepository $tournamentRepository): Response
    {
        $today = new \DateTime('NOW');
        $tournaments = $tournamentRepository->findAll();
        $upcoming = array_filter($tournaments, function($t) use ($today) {
            return ( $t->getStartDate() > $today );
        });
        $in_progress = array_filter($tournaments, function($t) use ($today) {
            return ( $t->getStartDate() < $today && $today < $t->getEndDate() );
        });
        $past = array_filter($tournaments, function($t) use ($today) {
            return ( $t->getEndDate() < $today );
        });

        return $this->render('tournament/index.html.twig', [
            'upcoming_tournaments' => $upcoming,
            'in_progress_tournaments' => $in_progress,
            'past_tournaments' => $past
        ]);
    }

    /**
     * @Route("/new", name="tournament_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_TO')")
     */
    public function new(Request $request): Response
    {
        $tournament = new Tournament();
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $draft = new Draft();
            $draft->setTournament($tournament);
            $draft->setInviteToken(bin2hex(random_bytes(8)));
            $entityManager->persist($draft);
            foreach($tournament->getTeams() as $team) {
                foreach($team->getMembers() as $user) {
                    $tournament->addUser($user);
                }
            }
            $entityManager->persist($tournament);
            $entityManager->flush();

            return $this->redirectToRoute('tournament_edit', ['id'=>$tournament->getId()]);
        }

        return $this->render('tournament/new.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="tournament_show", methods={"GET"})
     */
    public function show(Tournament $tournament, TwitchChecker $twitchChecker): Response
    {
        $leadingTeams = false;
        $leadingPlayers = false;
        $recentScores = false;
        $liveStreams = false;

        if ($tournament->isInProgress()) {
            $scoreRepo = $this->getDoctrine()->getRepository('App\Entity\Score');
            $leadingTeams = $scoreRepo->findTeamScores($tournament, null, 1);
            $leadingPlayers = $scoreRepo->findIndividualScores($tournament, 1);
            $recentScores = $scoreRepo->findRecentScores($tournament);
            $liveStreams = $twitchChecker->getLiveTwitchStreams($tournament);
        }

        return $this->render('tournament/show.html.twig', [
            'tournament' => $tournament,
            'leading_players' => $leadingPlayers,
            'leading_teams' => $leadingTeams,
            'recent_scores' => $recentScores,
            'live_streams' => $liveStreams
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tournament_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_TO')")
     */
    public function edit(Request $request, Tournament $tournament): Response
    {
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            foreach($tournament->getTeams() as $team) {
                foreach($team->getMembers() as $user) {
                    $tournament->addUser($user);
                }
            }
            $em->persist($tournament);
            $em->flush();

            return $this->redirectToRoute('tournament_index', [
                'id' => $tournament->getId(),
            ]);
        }

        return $this->render('tournament/edit.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/scoring", name="tournament_scoring", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_TO')")
     */
    public function scoring(Request $request, Tournament $tournament): Response
    {
        $forms = $this->get('form.factory');
        $count = $tournament->getUsers()->count();
        $form = $forms->createNamedBuilder('scoring_table');
        $place = 1;
        $scoringTable = $tournament->getScoringTable();
        while ($place <= $count) {
            if ( isset( $scoringTable[$place] ) ) {
                $options = ['data' => $scoringTable[$place]];
            } else {
                $options = [];
            }
            $form = $form->add($place, IntegerType::class, $options);
            $place++;
        }
        $form = $form->add('save', SubmitType::class)->getForm();

        $options = [
            'data' => $tournament->getCutoffDate(),
            'label' => 'Cutoff Date',
            'widget' => 'single_text'
        ];
        $cutoffForm = $forms->createNamedBuilder('cutoff_date')
            ->add('cutoff', DateType::class, $options)
            ->add('cutoff_line', IntegerType::class, [
                'data' => $tournament->getCutoffLine()
            ])
            ->add('cutoff_score', IntegerType::class, [
                'data' => $tournament->getCutoffScore()
            ])
            ->add('noshow_score', IntegerType::class, [
                'data' => $tournament->getNoshowScore()
            ])
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        $cutoffForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $scoringTable = $form->getData();
            $tournament->setScoringTable($scoringTable);
            $em->persist($tournament);
            $em->flush();
        } else if ($cutoffForm->isSubmitted() && $cutoffForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $cutoffForm->getData();
            $tournament->setCutoffDate($data['cutoff']);
            $tournament->setCutoffLine($data['cutoff_line']);
            $tournament->setCutoffScore($data['cutoff_score']);
            $tournament->setNoshowScore($data['noshow_score']);
            $em->persist($tournament);
            $em->flush();
        }

        return $this->render('tournament/scoring.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView(),
            'cutoffForm' => $cutoffForm->createView()
        ]);
    }

    /**
     * @Route("/{id}/scores/{game}", name="tournament_scores", methods={"GET","POST"})
     */
    public function game(Request $request, Tournament $tournament, Game $game)
    {
        $scoreRepo = $this->getDoctrine()
            ->getRepository('App\Entity\Score');
        
        $scores = $scoreRepo->findByGameAndTournament($tournament->getId(), $game);

        $teams = $scoreRepo->findTeamScores($tournament, $game);

        $user_score = null;
        $user = $this->getUser();
        if (!empty($user)) {
            $user_score = $this->getDoctrine()
                ->getRepository('App\Entity\Score')
                ->findOneBy([
                    'game' => $game,
                    'tournament' => $tournament,
                    'user' => $user
                ])
            ;
        }

        return $this->render('tournament/show_scores.html.twig', [
            'scores' => $scores,
            'game' => $game,
            'tournament' => $tournament,
            'teams' => $teams,
            'user_score' => $user_score
        ]);
    }

    /**
     * @Route("/{tournament}/leaderboards/team", name="leaderboard_team", methods={"GET"})
     */
    public function team_leaderboard(Request $request, Tournament $tournament)
    {
        $teamScores = $this->getDoctrine()
            ->getRepository('App\Entity\Score')
            ->findTeamScores($tournament);

        return $this->render('tournament/leaderboard.team.html.twig', [
            'tournament' => $tournament,
            'teamScores' => $teamScores 
        ]);
    }

    /**
     * @Route("/{tournament}/leaderboards/individual", name="leaderboard_individual", methods={"GET"})
     */
    public function individual_leaderboard(Request $request, Tournament $tournament)
    {
        if ($currentUser = $this->getUser()) {
            $user = $currentUser->getId();
        } else {
            $user = null;
        }

        $scores = $this->getDoctrine()
            ->getRepository('App\Entity\Score')
            ->findIndividualScores($tournament);

        return $this->render('tournament/leaderboard.individual.html.twig', [
            'user' => $user,
            'tournament' => $tournament,
            'scores' => $scores,
        ]);
    }

    /**
     * @Route("/{id}", name="tournament_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Tournament $tournament): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tournament->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($tournament);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tournament_index');
    }
}
