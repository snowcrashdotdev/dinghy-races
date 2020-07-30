<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TournamentRepository::class)
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $start_date;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $end_date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $format;

    /**
     * @ORM\ManyToMany(targetEntity=Game::class, inversedBy="tournaments")
     * @ORM\OrderBy({"description" = "ASC"})
     */
    private $games;

    /**
     * @ORM\OneToMany(targetEntity=Team::class, mappedBy="tournament", orphanRemoval=true)
     */
    private $teams;

    /**
     * @ORM\OneToOne(targetEntity=Draft::class, mappedBy="tournament", fetch="LAZY")
     */
    private $draft;

    /**
     * @ORM\OneToOne(targetEntity=TournamentScoring::class, mappedBy="tournament")
     */
    private $scoring;

    /**
     * @ORM\OneToMany(targetEntity=TournamentScore::class, mappedBy="tournament")
     * @ORM\OrderBy({"points" = "DESC", "updated_at" = "DESC"})
     */
    private $scores;

    /**
     * @ORM\OneToMany(targetEntity=TournamentUser::class, mappedBy="tournament")
     * @ORM\OrderBy({"ranked_points" = "DESC", "created_at" = "DESC"})
     */
    private $users;

    public const FORMATS = [ 'TEAM', 'INDIVIDUAL' ];

    public function __toString(): ?string
    {
        return $this->getTitle();
    }

    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->users = new ArrayCollection();
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

    public function hasAlreadyStarted()
    {
        return ($this->isInProgress() || $this->hasEnded());
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

    public function addUser(TournamentUser $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setTournament($this);
        }

        return $this;
    }

    public function removeUser(TournamentUser $user): self
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
        return (date_create('NOW') > $this->getScoring()->getDeadline());
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

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }
}
