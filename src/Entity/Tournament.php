<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TournamentRepository")
 *
 */
class Tournament
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="date")
     */
    private $start_date;

    /**
     * @ORM\Column(type="date")
     */
    private $end_date;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Game", inversedBy="tournaments")
     */
    private $games;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Team", mappedBy="tournament", orphanRemoval=true, cascade={"persist","remove"})
     * @ORM\OrderBy({"points" = "DESC", "name" = "ASC"})
     */
    private $teams;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="tournaments", fetch="EAGER")
     */
    private $users;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Draft", mappedBy="tournament", cascade={"persist", "remove"})
     */
    private $draft;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\TournamentScoring", mappedBy="tournament", cascade={"persist", "remove"})
     */
    private $scoring;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TournamentScore", mappedBy="tournament")
     */
    private $scores;

    public function __toString(): ?string
    {
        return $this->getTitle();
    }

    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->users = new ArrayCollection();

        $this->draft = new Draft();
        $this->draft->setTournament($this);
        $this->scoring = new TournamentScoring();
        $this->scoring->setTournament($this);
        $this->scores = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function hasEnded(): bool
    {
        return (date_create('NOW') > $this->getEndDate());
    }

    public function isUpcoming()
    {
        return ($this->getStartDate() > date_create('NOW'));
    }

    public function isInProgress() {
        $now = new \DateTime('now');
        return (
            $this->getStartDate() < $now and
            $this->getEndDate() > $now
        );
    }

    /**
     * @return Collection|Game[]
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): self
    {
        if (!$this->games->contains($game)) {
            $this->games[] = $game;
        }

        return $this;
    }

    public function removeGame(Game $game): self
    {
        if ($this->games->contains($game)) {
            $this->games->removeElement($game);
        }

        return $this;
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
            $team->setTournament($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
            // set the owning side to null (unless already changed)
            if ($team->getTournament() === $this) {
                $team->setTournament(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addTournament($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeTournament($this);
        }

        return $this;
    }

    public function hasScores() {
        return ! $this->getScores()->isEmpty();
    }

    public function getDraft(): ?Draft
    {
        return $this->draft;
    }

    public function setDraft(?Draft $draft): self
    {
        $this->draft = $draft;

        // set (or unset) the owning side of the relation if necessary
        $newTournament = $draft === null ? null : $this;
        if ($newTournament !== $draft->getTournament()) {
            $draft->setTournament($newTournament);
        }

        return $this;
    }

    public function hasEntered(User $user)
    {
        if (null === $draft = $this->getDraft()) {
            return false;
        } else {
            return $draft->hasEntered($user);
        }
    }

    public function getScoring(): ?TournamentScoring
    {
        return $this->scoring;
    }

    public function setScoring(?TournamentScoring $scoring): self
    {
        $this->scoring = $scoring;

        // set (or unset) the owning side of the relation if necessary
        $newTournament = null === $scoring ? null : $this;
        if ($scoring->getTournament() !== $newTournament) {
            $scoring->setTournament($newTournament);
        }

        return $this;
    }

    public function isAfterCutoff()
    {
        return (date_create('NOW') > $this->getScoring()->getCutoffDate());
    }

    public function getParticipationRate()
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->neq('auto_assigned', true));

        $expectedScoreCount = $this->getUsers()->count() * $this->getGames()->count();
        $actualScoreCount = $this->getScores()->matching($criteria)->count();
        return $actualScoreCount / $expectedScoreCount;
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
            $score->setTournament($this);
        }

        return $this;
    }

    public function removeScore(TournamentScore $score): self
    {
        if ($this->scores->contains($score)) {
            $this->scores->removeElement($score);
            // set the owning side to null (unless already changed)
            if ($score->getTournament() === $this) {
                $score->setTournament(null);
            }
        }

        return $this;
    }
}
