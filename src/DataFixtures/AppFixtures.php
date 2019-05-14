<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\DataFixtures\UserFixtures;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

use App\Entity\Tournament;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $upcoming_tournament = new Tournament();
        $upcoming_tournament->setTitle('La Calice Cup');
        $upcoming_tournament->setDescription('<p>32-person open entry STG tournament, where teams compete for score over a one-month period.</p>');
        $upcoming_tournament->setStartDate(new \DateTime('2019-06-01'));
        $upcoming_tournament->setEndDate(new \DateTime('2019-06-30'));

        $manager->persist($upcoming_tournament);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['dev'];
    }
}
