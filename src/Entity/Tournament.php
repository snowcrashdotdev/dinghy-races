<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repository\TournamentRepository")
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
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Game", inversedBy="tournaments")
     */
    private $games;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Score", mappedBy="tournament", orphanRemoval=true)
     */
    private $scores;

    /**
     * @ORM\Column(type="date")
     */
    private $start_date;

    /**
     * @ORM\Column(type="date")
     */
    private $end_date;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Team", mappedBy="tournament", orphanRemoval=true, cascade={"persist","remove"})
     * @ORM\OrderBy({"points" = "DESC"})
     */
    private $teams;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="tournaments")
     */
    private $users;

    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->scores = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->users = new ArrayCollection();
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
            $score->setTournament($this);
        }

        return $this;
    }

    public function removeScore(Score $score): self
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

    public function getTeamByUser(User $user): Team
    {
        return $this->getTeams()
            ->filter(function($team) use ($user){
                return $team->getMembers()->contains($user);
            })->first();
    }

    public function getScoresByUser(User $user)
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('user', $user));
        return $this->getScores()->matching($criteria);
    }

    public function getPoints(int $rank)
    {
        switch(true) {
            case ($rank >= 30):
                return 24 - ($rank - 30);
            case ($rank >= 13):
                return 58 - ($rank - 13) * 2;
            case ($rank >= 4):
                return 85 - ($rank - 4) * 3;
            case ($rank >= 0):
                return 100 - $rank * 5;
        }
    }

    public function getHighscore(Game $game)
    {
        $exp = new Comparison('game', '=', $game);
        $criteria = new Criteria();
        $criteria->andWhere($exp)->orderBy(['points' => Criteria::DESC])->setMaxResults(1);
        
        return $this->getScores()->matching($criteria)->first();
    }

    public function getWinner()
    {
        $criteria = new Criteria();
        $criteria->orderBy(['points'=> Criteria::DESC])->setMaxResults(1);
        return $this->getTeams()->matching($criteria)->first();
    }

    public function getScoreRank(Score $score) {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('game', $score->getGame()))
            ->orderBy(['points' => Criteria::DESC]);

        return $this->getScores()->matching($criteria)->indexOf($score);
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

    public function isInProgress() {
        $now = new \DateTime('now');
        return (
            $this->getStartDate() < $now and
            $this->getEndDate() > $now
        );
    }

    public function getIndividualScores() {
        $result = array();

        foreach ($this->getUsers() as $user) {
            $total = 0;
            $scores = $this->getScoresByUser($user);
            foreach($scores as $score) {
                $points = $this->getPoints($this->getScoreRank($score));
                $total += $points;
            }
            $result[] = ['user' => $user, 'points' => $total];
        }

        $users = array_column($result, 'user');
        $points = array_column($result, 'points');
        array_multisort($points, SORT_DESC, $users, SORT_ASC, $result);

        return $result;
    }

    public function getTopScorer() {
        if (!empty($scores = $this->getIndividualScores())) {
            return $scores[0];
        }
        return false;
    }
}
