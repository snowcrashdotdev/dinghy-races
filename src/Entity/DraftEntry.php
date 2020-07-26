<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DraftEntryRepository")
 */
class DraftEntry
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Draft", inversedBy="draftEntries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $draft;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="draftEntries", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $eligible;

    /**
     * @ORM\OneToOne(targetEntity=TournamentUser::class, cascade={"persist", "remove"})
     */
    private $tournament_user;

    public function __construct()
    {
        $this->created_at = date_create('NOW');
        $this->eligible = true;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDraft(): ?Draft
    {
        return $this->draft;
    }

    public function setDraft(?Draft $draft): self
    {
        $this->draft = $draft;

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

    public function getEligible(): ?bool
    {
        return $this->eligible;
    }

    public function setEligible(bool $eligible): self
    {
        $this->eligible = $eligible;

        return $this;
    }

    public function getTournamentUser(): ?TournamentUser
    {
        return $this->tournament_user;
    }

    public function setTournamentUser(?TournamentUser $tournament_user): self
    {
        $this->tournament_user = $tournament_user;

        return $this;
    }
}
