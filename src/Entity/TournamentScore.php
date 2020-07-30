<?php

namespace App\Entity;

use App\Repository\TournamentScoreRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TournamentScoreRepository::class)
 * @ORM\AssociationOverrides({
 *      @ORM\AssociationOverride(name="game", inversedBy="tournament_scores")
 * })
 */
class TournamentScore extends Score
{
    /**
     * @ORM\ManyToOne(targetEntity=Tournament::class, inversedBy="scores")
     */
    private $tournament;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="scores")
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity=TournamentUser::class, inversedBy="scores", fetch="EAGER")
     */
    private $user;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("public")
     */
    private $rank;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("public")
     */
    private $ranked_points;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("public")
     */
    private $team_points;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $auto_assigned;

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

    public function isNoShow(): bool
    {
        return $this->auto_assigned;
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

    /**
     * @Assert\IsTrue(message="Tournament scores must be submitted between the tournament's start and end dates")
     */
    public function isOnTime(): bool
    {
        return (
            $this->getUpdatedAt() > $this->tournament->getStartDate() &&
            $this->getUpdatedAt() < $this->tournament->getEndDate()
        );
    }

    public function getUser(): ?TournamentUser
    {
        return $this->user;
    }

    public function setUser(?TournamentUser $tournament_user): self
    {
        $this->user = $user;

        return $this;
    }
}