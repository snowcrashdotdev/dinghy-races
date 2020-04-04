<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProfileRepository")
 */
class Profile implements \Serializable
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
    
    private $picture_file;

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

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture)
    {
        $this->picture = $picture;

        return $this;
    }

    public function setPictureFile($file) : self
    {
        $this->picture_file = $file;

        return $this;
    }

    public function getPictureFile()
    {
        return $this->picture_file;
    }

    public function serialize()
    {
        return '';
    }

    public function unserialize($serialized)
    {
        return;
    }
}