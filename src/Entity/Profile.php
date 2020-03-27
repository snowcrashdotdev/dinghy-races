<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

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
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Url
     */
    private $social;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $picture;
    
    /**
     * @Assert\Image(
     *      allowLandscape = false,
     *      allowPortrait = false
     * )
     */
    protected $picture_file;

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

    public function getSocial()
    {
        return $this->social;
    }

    public function setSocial($social): self
    {
        $this->social = $social;

        return $this;
    }

    public function hasTwitch(): bool
    {
        return 1 === preg_match('~^https?://(www\.)?twitch\.tv/[a-zA-Z0-9_]{3,}/?$~', $this->getSocial());
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture)
    {
        $this->picture = $picture;

        return $this;
    }

    public function setPictureFile(?File $file)
    {
        $this->picture_file = $file;
    }

    public function getPictureFile() : ?File
    {
        return $this->picture_file;
    }
}