<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfileRepository")
 */
class Profile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", mappedBy="profile")
     */
    private $user;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $social = [];

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
    
        // set (or unset) the owning side of the relation if necessary
        $newProfile = $user === null ? null : $this;
        if ($newProfile !== $user->getProfile()) {
            $user->setProfile($newProfile);
        }
    
        return $this;
    }

    public function getSocial(): ?array
    {
        return $this->social;
    }

    public function setSocial(?array $social): self
    {
        $this->social = $social;

        return $this;
    }
}