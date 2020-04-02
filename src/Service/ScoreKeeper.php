<?php
namespace App\Service;

use App\Entity\Tournament;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;

class ScoreKeeper
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function scoreTournament(Tournament $tournament)
    {
        $games = $tournament->getGames();

        foreach($games as $game) {
            $this->scoreGame($tournament, $game, true);
        }
        $teams = $tournament->getTeams()->toArray();
        $scores = $tournament->getScores()->toArray();

        $this->scoreTeams($teams, $scores);
        $this->getManager()->flush();
    }

    public function scoreGame(Tournament $tournament, Game $game, $skip_team_scores = false)
    {
        $scores = $tournament->getScores()->toArray();
        $scores_game = array_filter($scores, function($score) use ($game) {
            return $score->getGame()->getId() === $game->getId();
        });
        $teams = $tournament->getTeams()->toArray();
        $scoring = $tournament->getScoring();
        $cutoff = $scoring->getCutoffLine();
        $cutoff_score = $scoring->getCutoffScore();
        $noshow_score = $scoring->getNoshowScore();
        $points_table = $scoring->getPointsTable();
        $points_table_team = $points_table;

        $scores_available = min(
            array_map(function($team) use ($cutoff) {
                return $team->getMembers()->count() - $cutoff;
            }, $teams)
        );

        foreach ($teams as $team) {
            $team->setScoresAvailable($scores_available);
        }

        $rank = 1;
        foreach ($scores_game as $score) {
            $score->setRank($rank);
            $rank++;

            if ($score->isNoShow()) {
                $score->setRankedPoints(0);
            } else {
                $score->setRankedPoints(array_shift($points_table));
            }

            if ($score->getTeam()->hasScoresAvailable()) {
                if ($score->isNoShow()) {
                    $score->setTeamPoints($noshow_score);
                } else {
                    $score->setTeamPoints(array_shift($points_table_team));
                }
                $score->getTeam()->useScore();
            } else {
                if ($score->isNoShow()) {
                    $score->setTeamPoints(0);
                } else {
                    $score->setTeamPoints($cutoff_score);
                }
            }
        }
        unset($rank);

        if (!$skip_team_scores) {
            $this->scoreTeams($teams, $scores);
        }

        $this->getManager()->flush();
    }

    private function scoreTeams(array $teams, array $scores) {
        foreach($teams as $team) {
            $team_scores = array_filter($scores, function($score) use ($team) {
                return $score->getTeam()->getId() === $team->getId();
            });

            $team->setPoints(
                array_reduce($team_scores, function($total, $score) {
                    return $total + $score->getTeamPoints();
                }, 0)
            );
        }
    }

    private function getManager(): EntityManagerInterface
    {
        return $this->manager;
    }
}