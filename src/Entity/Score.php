<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScoreRepository")
 */
class Score
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="scores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game", inversedBy="scores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $game;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tournament", inversedBy="scores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tournament;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\Column(type="bigint")
     */
    private $points;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="scores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $videoUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Image
     */
    private $screenshot;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *      max = 140,
     *      maxMessage = "Your comment should fit in a tweet."
     * )
     */
    private $comment;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $ranked_points;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $auto_assigned;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rank;

    /**
     * @ORM\Column(type="array")
     */
    private $points_history = [];

    /**
     * @ORM\Column(type="bigint")
     */
    private $team_points;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->date_submitted;
    }

    public function setCreatedAt(\DateTimeInterface $date_submitted): self
    {
        $this->date_submitted = $date_submitted;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): self
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    public function getScreenshot()
    {
        return $this->screenshot;
    }

    public function setScreenshot($screenshot): self
    {
        $this->screenshot = $screenshot;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if ($this->getTournament()->getStartDate() > new \DateTime('NOW')) {
            $context->buildViolation('The tournament has not started yet.')
                ->atPath('updated_at')
                ->addViolation()
            ;
            $this->setScreenshot(null);
            return;
        }

        if ($this->getTournament()->getEndDate() < new \DateTime('NOW')) {
            $context->buildViolation('The tournament has already concluded.')
                ->atPath('updated_at')
                ->addViolation()
            ;
            $this->setScreenshot(null);
            return;
        }
    }

    public function getRankedPoints(): ?string
    {
        return $this->ranked_points;
    }

    public function setRankedPoints(?string $ranked_points): self
    {
        $this->ranked_points = $ranked_points;

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

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getPointsHistory()
    {
        return $this->points_history;
    }

    public function setPointsHistory(array $points_history): self
    {
        $this->points_history = $points_history;

        return $this;
    }

    public function getTeamPoints(): ?string
    {
        return $this->team_points;
    }

    public function setTeamPoints(string $team_points): self
    {
        $this->team_points = $team_points;

        return $this;
    }
}