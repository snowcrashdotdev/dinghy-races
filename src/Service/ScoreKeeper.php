<?php
namespace App\Service;

use App\Entity\Tournament;
use App\Entity\TournamentScore;
use App\Entity\TournamentUser;
use App\Repository\TournamentScoreRepository;
use App\Entity\Game;
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
            $this->scoreGame($tournament, $game, true);
        }
        $teams = $tournament->getTeams()->toArray();
        $scores = $tournament->getScores()->toArray();

        $this->scoreTeams($teams, $scores);
        $this->getManager()->flush();
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

        $cutoff = $tournament->getScoring()->getCutoff();
        $cutoffScore = $tournament->getScoring()->getCutoffScore();
        $table = $tournament->getScoring()->getPointsTable();

        $allTournamentScores = $this->getScores()->findBy([
                'tournament' => $tournament
            ],
            ['rank' => 'ASC']
        );

        /**
         * Setup cutoff index and variables for scoring,
         * depending on tournament format.
         */
        if ($tournamentFormat === 'INDIVIDUAL') {
            $cutoffIndex = count($tournament->getGames()) - $cutoff;
        } else {
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
            foreach($allGameScores as $score) {
                $rank = $score->getRank();
                $rankedPoints = $table[$rank];
                $score->setRankedPoints($rankedPoints);
            }
        }

        /**
         * Score the game.
         */
        foreach($allGameScores as $score) {
            if ($tournamentFormat === 'INDIVIDUAL') {
                $user = $score->getUser();
                $userScores = array_filter($allTournamentScores, $this->filterUser($user));

                if (array_search($score, $userScores) < $cutoffIndex) {
                    $score->setRankedPoints(array_shift($table));
                } else {
                    $score->setRankedPoints($cutoffScore);
                }
            } else {
                $team = $score->getTeam();
                if ($team->hasScoresAvailable()) {
                    $team->useScore();
                    $score->setTeamPoints(array_shift($table));
                } else {
                    $score->setTeamPoints($cutoffScore);
                }
            }
        }
        if ($tournamentFormat === 'TEAM') {
            $total = 0;
            foreach($teams as $team) {
                $scores = array_filter($allTournamentScores, $this->filterTeam($team));
                $total = array_sum(
                    array_map([$this, 'returnTeamPoints'], $sccores)
                );
                $team->setPoints($total);
            }
        }
        $this->getManager()->flush();

        $this->getScores()->updateAggregateStatsFor($tournament);
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
            return $score->getTeam()->getId() === $team->getId();
        };
    }

    private function filterUser(TournamentUser $user) {
        return function(TournamentScore $score) use ($user) {
            return $score->getUser()->getId() === $user->getId();
        };
    }

    protected function returnTeamPoints(TournamentScore $score) {
        return $score->getTeamPoints();
    }
}