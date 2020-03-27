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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $marquee;

    protected $marquee_file;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TournamentScore", mappedBy="game")
     */
    private $tournament_scores;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PersonalBest", mappedBy="game")
     */
    private $personal_bests;

    public function __construct()
    {
        $this->tournaments = new ArrayCollection();
        $this->tournament_scores = new ArrayCollection();
        $this->personal_bests = new ArrayCollection();
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

    public function getMarquee(): ?string
    {
        return $this->marquee;
    }

    public function setMarquee(?string $marquee): self
    {
        $this->marquee = $marquee;

        return $this;
    }

    public function getMarqueeFile()
    {
        return $this->marquee_file;
    }

    public function setMarqueeFile($file)
    {
        $this->marquee_file = $file;

        return $this;
    }

    /**
     * @return Collection|TournamentScore[]
     */
    public function getTournamentScores(): Collection
    {
        return $this->tournament_scores;
    }

    public function addTournamentScore(TournamentScore $tournamentScore): self
    {
        if (!$this->tournament_scores->contains($tournamentScore)) {
            $this->tournament_scores[] = $tournamentScore;
            $tournamentScore->setGame($this);
        }

        return $this;
    }

    public function removeTournamentScore(TournamentScore $tournamentScore): self
    {
        if ($this->tournament_scores->contains($tournamentScore)) {
            $this->tournament_scores->removeElement($tournamentScore);
            // set the owning side to null (unless already changed)
            if ($tournamentScore->getGame() === $this) {
                $tournamentScore->setGame(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PersonalBest[]
     */
    public function getPersonalBests(): Collection
    {
        return $this->personal_bests;
    }

    public function addPersonalBest(PersonalBest $personalBest): self
    {
        if (!$this->personal_bests->contains($personalBest)) {
            $this->personal_bests[] = $personalBest;
            $personalBest->setGame($this);
        }

        return $this;
    }

    public function removePersonalBest(PersonalBest $personalBest): self
    {
        if ($this->personal_bests->contains($personalBest)) {
            $this->personal_bests->removeElement($personalBest);
            // set the owning side to null (unless already changed)
            if ($personalBest->getGame() === $this) {
                $personalBest->setGame(null);
            }
        }

        return $this;
    }
}
