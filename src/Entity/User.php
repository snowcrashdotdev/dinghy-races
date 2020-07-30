<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Profile;
use App\Entity\Tournament;
use App\Entity\Game;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(
 *      fields={"username", "email"},
 *      message="You may have already registered an account."
 * )
 */
class User implements UserInterface
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
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank
     * @Assert\Regex(
     *      pattern="/^[A-Za-z][A-Za-z0-9]*(?:_[A-Za-z0-9]+)*$/",
     *      match="true",
     *      message="Your name must start with a letter and contain only letters, numbers, and underscores."
     * )
     * @Groups("public")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $verified;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reset_token;

    /**
     * @ORM\OneToOne(targetEntity=Profile::class, inversedBy="user", fetch="EAGER")
     */
    private $profile;

    /**
     * @ORM\OneToMany(targetEntity=PersonalBest::class, mappedBy="user")
     * @ORM\OrderBy({"updated_at" = "DESC"})
     */
    private $personal_bests;

    /**
     * @ORM\OneToMany(targetEntity=TournamentUser::class, mappedBy="user")
     */
    private $appearances;

    public function __construct()
    {
        $this->created_at = new \DateTime('now');
        $this->roles = ["ROLE_USER"];
        $this->verified = false;

        $this->teams = new ArrayCollection();
        $this->tournaments = new ArrayCollection();
        $this->draftEntries = new ArrayCollection();

        $this->profile = new Profile();
        $this->profile->setUser($this);
        $this->tournament_scores = new ArrayCollection();
        $this->personal_bests = new ArrayCollection();
        $this->appearances = new ArrayCollection();
    }

    public function __toString()
    {
        return strtolower($this->getUsername());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRoles(array $roles): self
    {
        $this->roles = array_unique(array_merge($roles, $this->roles));
        
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return $_ENV['AUTH_SALT'];
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
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
        }

        return $this;
    }

    public function removeTournament(Tournament $tournament): self
    {
        if ($this->tournaments->contains($tournament)) {
            $this->tournaments->removeElement($tournament);
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): self
    {
        $this->verified = $verified;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;

        return $this;
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

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return Collection|DraftEntry[]
     */
    public function getDraftEntries(): Collection
    {
        return $this->draftEntries;
    }

    public function addDraftEntry(DraftEntry $draftEntry): self
    {
        if (!$this->draftEntries->contains($draftEntry)) {
            $this->draftEntries[] = $draftEntry;
            $draftEntry->setUser($this);
        }

        return $this;
    }

    public function removeDraftEntry(DraftEntry $draftEntry): self
    {
        if ($this->draftEntries->contains($draftEntry)) {
            $this->draftEntries->removeElement($draftEntry);
            // set the owning side to null (unless already changed)
            if ($draftEntry->getUser() === $this) {
                $draftEntry->setUser(null);
            }
        }

        return $this;
    }

    public function setEligibility(Tournament $tournament, ?bool $eligibility): self
    {
        $draft = $tournament->getDraft();
        $criteria = new Criteria();
        $expr = new Comparison('draft', '=', $draft);
        $criteria->where($expr);

        $entry = $this->getDraftEntries()->matching($criteria)->first();

        if ($entry) {
            $entry->setEligible($eligibility);
        }

        return $this;
    }

    public function isInTournament(Tournament $tournament): bool
    {
        $criteria = new Criteria();
        $expr = new Comparison('tournament', '=', $tournament);
        $criteria->where($expr);

        return ! $this->getAppearances()->matching($criteria)->isEmpty();
    }

    public function getScore(Tournament $tournament, Game $game)
    {
        $criteria = new Criteria();
        $expr = new Comparison('tournament', '=', $tournament);
        $expr2 = new Comparison('game', '=', $game);
        $criteria->where($expr)->andWhere($expr2);
        return $this->getTournamentScores()->matching($criteria)->first();
    }

    public function getTeam(Tournament $tournament)
    {
        return $this->getTeams()->filter(function($t) use ($tournament) {
            return $t->getTournament() === $tournament;
        })->first();
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
            $tournament = $team->getTournament();
            $this->teams[] = $team;
            $this->setEligibility($tournament, false);
            $this->addTournament($tournament);
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->contains($team)) {
            $tournament = $team->getTournament();
            $this->teams->removeElement($team);
            $this->setEligibility($tournament, true);
            $this->removeTournament($tournament);
        }

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
            $tournamentScore->setUser($this);
        }

        return $this;
    }

    public function removeTournamentScore(TournamentScore $tournamentScore): self
    {
        if ($this->tournament_scores->contains($tournamentScore)) {
            $this->tournament_scores->removeElement($tournamentScore);
            // set the owning side to null (unless already changed)
            if ($tournamentScore->getUser() === $this) {
                $tournamentScore->setUser(null);
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
            $personalBest->setUser($this);
        }

        return $this;
    }

    public function removePersonalBest(PersonalBest $personalBest): self
    {
        if ($this->personal_bests->contains($personalBest)) {
            $this->personal_bests->removeElement($personalBest);
            // set the owning side to null (unless already changed)
            if ($personalBest->getUser() === $this) {
                $personalBest->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TournamentUser[]
     */
    public function getAppearances(): Collection
    {
        return $this->appearances;
    }

    public function addAppearance(TournamentUser $appearance): self
    {
        if (!$this->appearances->contains($appearance)) {
            $this->appearances[] = $appearance;
            $appearance->setUser($this);
        }

        return $this;
    }

    public function removeAppearance(TournamentUser $appearance): self
    {
        if ($this->appearances->contains($appearance)) {
            $this->appearances->removeElement($appearance);
            // set the owning side to null (unless already changed)
            if ($appearance->getUser() === $this) {
                $appearance->setUser(null);
            }
        }

        return $this;
    }
}
