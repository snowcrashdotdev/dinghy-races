<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonalBestRepository")
 */
class PersonalBest
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="personalBests")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game", inversedBy="personalBests")
     */
    private $game;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $video_url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $screenshot;

    /**
     * @ORM\Column(type="string", length=144, nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="bigint")
     */
    private $points;

    /**
     * @ORM\Column(type="array")
     */
    private $points_history = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

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

    public function getVideoUrl(): ?string
    {
        return $this->video_url;
    }

    public function setVideoUrl(?string $video_url): self
    {
        $this->video_url = $video_url;

        return $this;
    }

    public function getScreenshot(): ?string
    {
        return $this->screenshot;
    }

    public function setScreenshot(?string $screenshot): self
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

    public function getPoints(): ?string
    {
        return $this->points;
    }

    public function setPoints(string $points): self
    {
        $this->points = $points;

        return $this;
    }

    public function getPointsHistory(): ?array
    {
        return $this->points_history;
    }

    public function setPointsHistory(array $points_history): self
    {
        $this->points_history = $points_history;

        return $this;
    }
}
