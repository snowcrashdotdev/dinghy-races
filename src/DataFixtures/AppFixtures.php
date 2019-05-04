<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\DataFixtures\UserFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use App\Entity\User;
use App\Entity\Tournament;
use App\Entity\Game;
use App\Entity\Score;
use App\Entity\Team;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $upcoming_tournament = new Tournament();
        $upcoming_tournament->setTitle('La Calice Cup');
        $upcoming_tournament->setDescription('32-person open entry STG tournament, where teams compete for score over a one-month period.');
        $upcoming_tournament->setStartDate(new \DateTime('2019-06-01'));
        $upcoming_tournament->setEndDate(new \DateTime('2019-06-30'));

        $manager->persist($upcoming_tournament);

        $past_tournament = new Tournament();
        $past_tournament->setTitle('2019 NFL Playoffs');
        $past_tournament->setDescription('Tom Brady ya bitch.');
        $past_tournament->setEndDate(new \DateTime('-1 MONTH'));
        $past_tournament->setStartDate(new \DateTime('-2 MONTH'));

        $manager->persist($past_tournament);

        $in_progress_tournament = new Tournament();
        $in_progress_tournament->setTitle('Kappa Open 2019');
        $in_progress_tournament->setDescription('Put your database to the test.');
        $in_progress_tournament->setStartDate(new \DateTime('-15 DAY'));
        $in_progress_tournament->setEndDate(new \DateTime('+15 DAY'));

        $manager->persist($in_progress_tournament);

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class
        );
    }
}
