<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Game;

class MameFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        if (false !== $dat = @simplexml_load_file('var/mame.xml')) {
            foreach($dat->game as $rom) {
                $game = new Game();
                $game->setName((string)$rom->attributes()->{'name'});
                $game->setDescription(strval($rom->description));
                $game->setYear(strval($rom->year));
                $game->setManufacturer(strval($rom->manufacturer));
                $manager->persist($game);
            }
        }
        $manager->flush();
    }
}
