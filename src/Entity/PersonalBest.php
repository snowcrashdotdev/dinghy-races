<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonalBestRepository")
 * @ORM\AssociationOverrides({
 *      @ORM\AssociationOverride(name="user", inversedBy="personal_bests"),
 *      @ORM\AssociationOverride(name="game", inversedBy="personal_bests")
 * })
 */
class PersonalBest extends Score
{
    public function __construct()
    {
        parent::__construct();
    }
}
