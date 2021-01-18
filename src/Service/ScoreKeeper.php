<?php
namespace App\Service;

use App\Entity\Tournament;
use App\Entity\TournamentScore;
use App\Entity\TournamentUser;
use App\Repository\TournamentScoreRepository;
use App\Entity\Game;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;

class ScoreKeeper
{
    private $manager;

    public function __construct(EntityManagerInterface $manager, TournamentScoreRepository $scores)
    {
        $this->manager = $manager;
        $this->scores = $scores;
    }

    public function scoreTournament(Tournament $tournament)
    {
        $games = $tournament->getGames();

        foreach($games as $game) {
            $scores = $this->getScores()->findBy([
                'tournament' => $tournament,
                'game' => $game
            ], ['points' => 'DESC', 'created_at' => 'ASC']);

            /**
             * Reset scores ranks.
             */
            foreach($scores as $i => $score) {
                $rank = $i + 1;
                $score->setRank($rank);
            }
            $this->getManager()->flush();

            $this->assignRankedPointsFor($tournament, $scores);
        }

        /**
         * Execute query that updates totals and averages
         * for each tournament user.
         */
        $this->getScores()->updateAggregateStatsFor($tournament);
    }

    public function update(TournamentScore $newScore)
    {
        $tournament = $newScore->getTournament();
        $tournamentFormat = $tournament->getFormat();
        $game = $newScore->getGame();

        $allGameScores = $this->getScores()->findBy([
            'tournament' => $tournament,
            'game' => $game
        ], ['points' => 'DESC', 'created_at' => 'ASC']);

        $newScoreIndex = array_search($newScore, $allGameScores);
        $newScoreRank = 1 + $newScoreIndex;

        $newScore->setRank($newScoreRank);

        /**
         * Only update rank for scores below new value.
         */
        for ($i = $newScoreRank; $i < count($allGameScores); $i++) {
            $score = $allGameScores[$i];
            $rank = $i + 1;
            $score->setRank($rank);
        }
        $this->getManager()->flush();

        $this->assignRankedPointsFor($tournament, $allGameScores);

        /**
         * Execute query that updates totals and averages
         * for each tournament user.
         */
        $this->getScores()->updateAggregateStatsFor($tournament);
    }

    private function assignRankedPointsFor(Tournament $tournament, array $scores)
    {
        $tournamentFormat = $tournament->getFormat();
        $cutoff = $tournament->getScoring()->getCutoff();
        $cutoffScore = $tournament->getScoring()->getCutoffScore();
        $table = $tournament->getScoring()->getPointsTable();
        $allTournamentScores = $this->getScores()
            ->findBy([
                'tournament' => $tournament
            ],
            ['rank' => 'ASC']
        );

        /**
         * Setup cutoff index and variables for scoring,
         * depending on tournament format.
         */
        if ($tournamentFormat === 'TEAM') {
            $teams = $tournament->getTeams()->toArray();
            $cutoffIndex = min(
                array_map(function($team) use ($cutoff) {
                    return $team->getMembers()->count() - $cutoff;
                }, $teams)
            );

            /**
             * Setup each team with all their available scores.
             */
            foreach($teams as $team) {
                $team->setScoresAvailable($cutoffIndex);
            }

            /**
             * Immediately assign team tournament scores ranked points
             * for their given rank (ez).
             */
            foreach($scores as $score) {
                $rank = $score->getRank();
                $rankedPoints = $table[$rank];
                $score->setRankedPoints($rankedPoints);
            }
        } else {
            $cutoffIndex = count($tournament->getGames()) - $cutoff;
        }

        /**
         * Score the game.
         */
        foreach($scores as $score) {
            if ($tournamentFormat === 'TEAM') {
                $team = $score->getTeam();
                if ($team->hasScoresAvailable()) {
                    $team->useScore();
                    $score->setTeamPoints(array_shift($table));
                } else {
                    $score->setTeamPoints($cutoffScore);
                }
            } else {
                $user = $score->getUser();
                $userScores = array_values(
                        array_filter(
                        $allTournamentScores,
                        $this->filterUser($user)
                    )
                );

                if (array_search($score, $userScores) < $cutoffIndex) {
                    $score->setRankedPoints(array_shift($table));
                } else {
                    $score->setRankedPoints($cutoffScore);
                }
            }
        }

        /**
         * Sum team points for each team.
         */
        if ($tournamentFormat === 'TEAM') {
            $total = 0;
            foreach($teams as $team) {
                $team_scores = array_filter($allTournamentScores, $this->filterTeam($team));
                $total = array_sum(
                    array_map([$this, 'returnTeamPoints'], $team_scores)
                );
                $team->setPoints($total);
            }
        }

        $this->getManager()->flush();
    }

    private function getManager(): EntityManagerInterface
    {
        return $this->manager;
    }

    private function getScores(): TournamentScoreRepository
    {
        return $this->scores;
    }

    private function filterTeam(Team $team) {
        return function(TournamentScore $score) use ($team) {
            return $score->getTeam() === $team;
        };
    }

    private function filterUser(TournamentUser $user) {
        return function(TournamentScore $score) use ($user) {
            return $score->getUser() === $user;
        };
    }

    protected function returnTeamPoints(TournamentScore $score) {
        return $score->getTeamPoints();
    }
}