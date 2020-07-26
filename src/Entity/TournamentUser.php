<?php

namespace App\Entity;

use App\Repository\TournamentUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TournamentUserRepository::class)
 */
class TournamentUser
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="appearances")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Tournament::class, inversedBy="tournamentUsers")
     */
    private $tournament;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="tournamentUsers")
     */
    private $team;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @ORM\OneToMany(targetEntity=TournamentScore::class, mappedBy="tournament_user")
     */
    private $tournamentScores;

    public function __construct()
    {
        $this->created_at = new \DateTime('now');
        $this->tournamentScores = new ArrayCollection();
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
    public function getTournamentScores(): Collection
    {
        return $this->tournamentScores;
    }

    public function addTournamentScore(TournamentScore $tournamentScore): self
    {
        if (!$this->tournamentScores->contains($tournamentScore)) {
            $this->tournamentScores[] = $tournamentScore;
            $tournamentScore->setTournamentUser($this);
        }

        return $this;
    }

    public function removeTournamentScore(TournamentScore $tournamentScore): self
    {
        if ($this->tournamentScores->contains($tournamentScore)) {
            $this->tournamentScores->removeElement($tournamentScore);
            // set the owning side to null (unless already changed)
            if ($tournamentScore->getTournamentUser() === $this) {
                $tournamentScore->setTournamentUser(null);
            }
        }

        return $this;
    }
}
