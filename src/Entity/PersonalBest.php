<?php

namespace App\Entity;

use App\Repository\PersonalBestRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PersonalBestRepository::class)
 * @ORM\AssociationOverrides({
 *      @ORM\AssociationOverride(name="game", inversedBy="personal_bests")
 * })
 */
class PersonalBest extends Score
{
    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="personal_bests")
     */
    private $user;

    public function __construct()
    {
        parent::__construct();
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

}
