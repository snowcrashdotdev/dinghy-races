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
    public function fromTournamentScore(TournamentScore $score): self
    {
        $this->setCreatedAt($score->getCreatedAt());
        $this->setUpdatedAt($score->getUpdatedAt());
        $this->setUser($score->getUser());
        $this->setGame($score->getGame());
        $this->setPoints($score->getPoints());
        $this->setPointsHistory($score->getPointsHistory());
        $this->setVideoUrl($score->getVideoUrl());
        $this->setScreenshot($score->getScreenshot());
        $this->setComment($score->getComment());

        return $this;
    }
}
