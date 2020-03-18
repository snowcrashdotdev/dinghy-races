<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
 */
class Game
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Score", mappedBy="game", orphanRemoval=true)
     */
    private $scores;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tournament", mappedBy="games")
     */
    private $tournaments;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $year;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $manufacturer;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PersonalBest", mappedBy="game")
     * @ORM\OrderBy({"points" = "DESC"})
     */
    private $personalBests;

    public function __construct()
    {
        $this->scores = new ArrayCollection();
        $this->tournaments = new ArrayCollection();
        $this->personalBests = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Score[]
     */
    public function getScores(): Collection
    {
        return $this->scores;
    }

    public function addScore(Score $score): self
    {
        if (!$this->scores->contains($score)) {
            $this->scores[] = $score;
            $score->setGame($this);
        }

        return $this;
    }

    public function removeScore(Score $score): self
    {
        if ($this->scores->contains($score)) {
            $this->scores->removeElement($score);
            // set the owning side to null (unless already changed)
            if ($score->getGame() === $this) {
                $score->setGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tournament[]
     */
    public function getTournaments(): Collection
    {
        return $this->tournaments;
    }

    public function addTournament(Tournament $tournament): self
    {
        if (!$this->tournaments->contains($tournament)) {
            $this->tournaments[] = $tournament;
            $tournament->addGame($this);
        }

        return $this;
    }

    public function removeTournament(Tournament $tournament): self
    {
        if ($this->tournaments->contains($tournament)) {
            $this->tournaments->removeElement($tournament);
            $tournament->removeGame($this);
        }

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(?string $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?string $manufacturer): self
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * @return Collection|PersonalBest[]
     */
    public function getPersonalBests(): Collection
    {
        return $this->personalBests;
    }

    public function addPersonalBest(PersonalBest $personalBest): self
    {
        if (!$this->personalBests->contains($personalBest)) {
            $this->personalBests[] = $personalBest;
            $personalBest->setGame($this);
        }

        return $this;
    }

    public function removePersonalBest(PersonalBest $personalBest): self
    {
        if ($this->personalBests->contains($personalBest)) {
            $this->personalBests->removeElement($personalBest);
            // set the owning side to null (unless already changed)
            if ($personalBest->getGame() === $this) {
                $personalBest->setGame(null);
            }
        }

        return $this;
    }
}
