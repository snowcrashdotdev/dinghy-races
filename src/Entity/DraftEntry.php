<?php

namespace App\Entity;

use App\Repository\DraftEntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DraftEntryRepository::class)
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
     * @ORM\Column(type="boolean")
     */
    private $eligible;

    /**
     * @ORM\ManyToOne(targetEntity=Draft::class, inversedBy="draftEntries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $draft;

    /**
     * @ORM\OneToOne(targetEntity=TournamentUser::class)
     */
    private $user;

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

    public function getUser(): ?TournamentUser
    {
        return $this->user;
    }

    public function setUser(?TournamentUser $user): self
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
}
