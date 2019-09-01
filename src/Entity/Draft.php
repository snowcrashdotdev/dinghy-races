<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DraftRepository")
 */
class Draft
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Tournament", inversedBy="draft", cascade={"persist", "remove"})
     */
    private $tournament;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User")
     */
    private $entries;

    /**
     * @ORM\Column(type="guid", nullable=true)
     */
    private $invite_token;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection|User[]
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function addEntry(User $entry): self
    {
        if (!$this->entries->contains($entry)) {
            $this->entries[] = $entry;
        }

        return $this;
    }

    public function removeEntry(User $entry): self
    {
        if ($this->entries->contains($entry)) {
            $this->entries->removeElement($entry);
        }

        return $this;
    }

    public function getInviteToken(): ?string
    {
        return $this->invite_token;
    }

    public function setInviteToken(?string $invite_token): self
    {
        $this->invite_token = $invite_token;

        return $this;
    }

    public function alreadyEntered(User $entry)
    {
        return ($this->entries->contains($entry));
    }
}
