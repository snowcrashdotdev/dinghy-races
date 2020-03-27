<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TournamentScoringRepository")
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
     * @ORM\OneToOne(targetEntity="App\Entity\Tournament", inversedBy="scoring", cascade={"persist", "remove"})
     */
    private $tournament;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $points_table = [];

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $cutoff_date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cutoff_line;

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

    public function getCutoffDate(): ?\DateTimeInterface
    {
        return $this->cutoff_date;
    }

    public function setCutoffDate(?\DateTimeInterface $cutoff_date): self
    {
        $this->cutoff_date = $cutoff_date;

        return $this;
    }

    public function getCutoffLine(): ?int
    {
        return $this->cutoff_line;
    }

    public function setCutoffLine(?int $cutoff_line): self
    {
        $this->cutoff_line = $cutoff_line;

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
