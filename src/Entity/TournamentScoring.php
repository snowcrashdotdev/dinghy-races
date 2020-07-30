<?php

namespace App\Entity;

use App\Repository\TournamentScoringRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TournamentScoringRepository::class)
 */
class TournamentScoring
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Tournament::class, inversedBy="scoring")
     */
    private $tournament;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $points_table = [];

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $deadline;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cutoff;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cutoff_score;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $noshow_score;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPointsTable(): ?array
    {
        return $this->points_table;
    }

    public function setPointsTable(?array $points_table): self
    {
        $this->points_table = $points_table;

        return $this;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getCutoff(): ?int
    {
        return $this->cutoff;
    }

    public function setCutoff(?int $cutoff): self
    {
        $this->cutoff = $cutoff;

        return $this;
    }

    public function getCutoffScore(): ?int
    {
        return $this->cutoff_score;
    }

    public function setCutoffScore(?int $cutoff_score): self
    {
        $this->cutoff_score = $cutoff_score;

        return $this;
    }

    public function getNoshowScore(): ?int
    {
        return $this->noshow_score;
    }

    public function setNoshowScore(?int $noshow_score): self
    {
        $this->noshow_score = $noshow_score;

        return $this;
    }
}
