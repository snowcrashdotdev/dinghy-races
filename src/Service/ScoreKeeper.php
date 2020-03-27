<?php
namespace App\Service;

use App\Entity\Tournament;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;

class ScoreKeeper
{
    private $tournament;
    private $em;

    public function __construct(Tournament $tournament, EntityManagerInterface $em)
    {
        $this->tournament = $tournament;
        $this->em = $em;
    }

    public function scoreTournament()
    {
        foreach($this->tournament->getGames() as $game) {
            $this->scoreGame($game);
        }
    }

    public function scoreGame(Game $game)
    {
        $scores = $this->em->getRepository('App\Entity\TournamentScore')
            ->findSubmittedTournamentScores($this->tournament, $game)
        ;
        $teams = $this->tournament->getTeams();

        $rank = 1;
        foreach ($scores as $score) {
            $score->setRank($rank);
            $rankedPoints = $this->getRankedPoints($rank);
            $score->setRankedPoints($rankedPoints);
            $this->em->persist($score);
            $rank++;
        }
        $this->em->flush();

        $cutoff_line = $this->tournament->getCutoffLine();
        $cutoff_score = $this->tournament->getCutoffScore();

        foreach ($teams as $team) {
            $cutoff = floor(
                (1 - $cutoff_line / 100) * $team->getMembers()->count()
            );
            $count = 1;

            $team_scores = array_filter($scores, function($score) use ($team) {
                return $score->getTeam()->getId() === $team->getId();
            });

            foreach ($team_scores as $score) {
                if ($count < $cutoff) {
                    $score->setTeamPoints($score->getRankedPoints());
                } else {
                    $score->setTeamPoints($cutoff_score);
                }
                $this->em->persist($score);
                $count++;   
            }
            $this->em->flush();

            $team_points = $this->em->getRepository('App\Entity\TournamentScore')
                ->findTotalTeamPoints($team);

            $team->setPoints($team_points);
            $this->em->persist($team);
        }
        $this->em->flush();
    }

    public function getRankedPoints(int $rank)
    {
        return $this->tournament->getScoringTable()[$rank];
    }
}