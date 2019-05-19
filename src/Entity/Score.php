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
    private $date_submitted;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_updated;

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
     */
    private $screenshot;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comment;

    public function __construct(Game $game, Tournament $tournament, User $user, Team $team)
    {
        $this->setGame($game);
        $this->setTournament($tournament);
        $this->setUser($user);
        $this->setTeam($team);
        $this->setDateSubmitted(new \DateTime('now'));
        $this->setDateUpdated(new \DateTime('now'));
    }

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

    public function getDateSubmitted(): ?\DateTimeInterface
    {
        return $this->date_submitted;
    }

    public function setDateSubmitted(\DateTimeInterface $date_submitted): self
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

    public function getDateUpdated(): ?\DateTimeInterface
    {
        return $this->date_updated;
    }

    public function setDateUpdated(\DateTimeInterface $date_updated): self
    {
        $this->date_updated = $date_updated;

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
        if ($this->getTournament()->getStartDate() > $this->getDateUpdated()) {
            $context->buildViolation('The tournament has not started yet.')
                ->atPath('date_updated')
                ->addViolation()
            ;
        }

        if ($this->getTournament()->getEndDate() < $this->getDateUpdated()) {
            $context->buildViolation('The tournament has already concluded.')
                ->atPath('date_updated')
                ->addViolation()
            ;
        }

        if (
            $this->getVideoUrl() === null and
            $this->getScreenshot() === null
        ) {
            $context->buildViolation('Either a Video URL or screenshot is required.')
                ->addViolation();
        }
    }
}