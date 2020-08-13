<?php

namespace App\Entity;

use App\Repository\TournamentUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TournamentUserRepository::class)
 */
class TournamentUser implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="appearances", fetch="EAGER")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Tournament::class, inversedBy="users")
     */
    private $tournament;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="members")
     */
    private $team;

    /**
     * @ORM\OneToMany(targetEntity=TournamentScore::class, mappedBy="user", fetch="LAZY", cascade={"persist", "remove"})
     * @ORM\OrderBy({"updated_at" = "DESC"})
     * @Groups("public")
     */
    private $scores;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("public")
     */
    private $avg_rank;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("public")
     */
    private $completion;

    public function __construct()
    {
        $this->created_at = new \DateTime('now');
        $this->scores = new ArrayCollection();
    }

    public function __toString(): ?string
    {
        return (string) $this->getUser();
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

    /**
     * @Groups("public")
     */
    public function getUsername(): ?string
    {
        return $this->getUser()->getUsername();
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
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection|TournamentScore[]
     */
    public function getScores(): Collection
    {
        return $this->scores;
    }

    public function addScore(TournamentScore $score): self
    {
        if (!$this->scores->contains($score)) {
            $this->scores[] = $score;
            $score->setUser($this);
        }

        return $this;
    }

    public function removeTournamentScore(TournamentScore $score): self
    {
        if ($this->scores->contains($score)) {
            $this->scores->removeElement($score);
            // set the owning side to null (unless already changed)
            if ($score->getUser() === $this) {
                $score->setUser(null);
            }
        }

        return $this;
    }

    public function getRankedPoints(): ?int
    {
        return $this->ranked_points;
    }

    public function setRankedPoints(int $ranked_points): self
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

    public function getAvgRank(): ?float
    {
        return $this->avg_rank;
    }

    public function setAvgRank(?float $avg_rank): self
    {
        $this->avg_rank = $avg_rank;

        return $this;
    }

    public function getCompletion(): ?float
    {
        return $this->completion;
    }

    public function setCompletion(?float $completion): self
    {
        $this->completion = $completion;

        return $this;
    }

    public function getPublicData()
    {
        $public_data = [
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'ranked_points' => $this->getRankedPoints(),
            'avg_rank' => $this->getAvgRank(),
            'completion' => $this->getCompletion()
        ];

        return $public_data;
    }

    public function jsonSerialize()
    {
        return $this->getPublicData();
    }
}
