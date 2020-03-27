<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TournamentScoreRepository")
 * @ORM\AssociationOverrides({
 *      @ORM\AssociationOverride(name="user", inversedBy="tournament_scores"),
 *      @ORM\AssociationOverride(name="game", inversedBy="tournament_scores")
 * })
 */
class TournamentScore extends Score
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rank;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ranked_points;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $team_points;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $auto_assigned;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tournament", inversedBy="scores")
     */
    private $tournament;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="scores")
     */
    private $team;

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getRankedPoints(): ?int
    {
        return $this->ranked_points;
    }

    public function setRankedPoints(?int $ranked_points): self
    {
        $this->ranked_points = $ranked_points;

        return $this;
    }

    public function getTeamPoints(): ?int
    {
        return $this->team_points;
    }

    public function setTeamPoints(?int $team_points): self
    {
        $this->team_points = $team_points;

        return $this;
    }

    public function getAutoAssigned(): ?bool
    {
        return $this->auto_assigned;
    }

    public function setAutoAssigned(?bool $auto_assigned): self
    {
        $this->auto_assigned = $auto_assigned;

        return $this;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): self
    {
        $this->tournament = $tournament;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }
}
