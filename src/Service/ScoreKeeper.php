<?php
namespace App\Service;

use App\Entity\Tournament;
use App\Entity\Game;
use App\Entity\Score;
use App\Entity\User;
use App\Entity\Team;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManagerInterface;

class ScoreKeeper
{
    private $tournament;
    private $em;

    public function __construct(Tournament $tournament, EntityManagerInterface $em)
    {
        $this->tournament = $tournament;
        $this->em = $em;

        if ($tournament->needsFullScoring()) {
            $this->scoreTournament();
        }

        if ($tournament->hasNoShows()) {
            $this->assignNoShowScores();
            $this->scoreTeams();
        }
    }

    public function scoreTournament()
    {
        $games = $this->tournament->getGames()->toArray();

        foreach($games as $game) {
            $this->scoreGame($game);
        }

        $this->scoreTeams();
    }

    public function scoreGame(Game $game)
    {
        $scores = $this->getScores($game);

        $rank = 1;
        foreach ($scores as $score) {
            $score->setRank($rank);
            $rankedPoints = $this->getRankedPoints($rank);
            $score->setRankedPoints($rankedPoints);
            $this->em->persist($score);
            $rank++;
        }
        $this->em->flush();
    }

    public function scoreTeams()
    {
        $teams = $this->tournament->getTeams()->toArray();
        foreach($teams as $team) {
            $scores = $team->getScores()->toArray();
            $points = 0;
            foreach ($scores as $score) { $points += $score->getRankedPoints(); }
            $team->setPoints($points);
            $this->em->persist($team);
        }
        $this->em->flush();
    }

    public function getRankedPoints(int $rank)
    {
        return $this->tournament->getScoringTable()[$rank];
    }

    public function getScores(Game $game = null, User $user = null, bool $ranked=null)
    {
        $criteria = new Criteria();
        if ($game) {
            $gExpr = new Comparison('game', '=', $game);
            $criteria->andWhere($gExpr);
        }

        if ($user) {
            $uExpr = new Comparison('user', '=', $user);
            $criteria->andWhere($uExpr);
        }

        $nExpr = new Comparison('auto_assigned', Comparison::NEQ, true);
        $criteria->andWhere($nExpr);

        if (true === $ranked) {
            $criteria->orderBy(['ranked_points' => 'DESC', 'date_updated' => 'ASC']);
        } else {
            $criteria->orderBy(['points' => 'DESC', 'date_updated' => 'ASC']);
        }

        $scores = $this->tournament->getScores()->matching($criteria);

        return $scores;
    }

    public function assignNoShowScores()
    {
        foreach($this->tournament->getGames() as $game) {
            $noShowPoints = $this->getScores($game, null, true)
                ->last()
                ->getRankedPoints() - 5;
            settype($noShowPoints, 'string');
            foreach($this->tournament->getTeams() as $team) {
                foreach($team->getMembers() as $user) {
                    if ($this->getScores($game, $user)->isEmpty()) {
                        $this->autoAssignScore($team, $game, $user, $noShowPoints);
                    }
                }
            }
        }
    }

    public function autoAssignScore(Team $team, Game $game, User $user, string $points)
    {
        $noShowScore = new Score($game, $this->tournament, $user, $team);
        $noShowScore->setAutoAssigned(true);
        $noShowScore->setPoints(0);
        $noShowScore->setRankedPoints($points);
        $noShowScore->setComment('(no show)');
        $this->em->persist($noShowScore);
        $this->em->flush();
    }
}