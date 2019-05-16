<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\DataFixtures\UserFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use App\Entity\Tournament;
use App\Entity\Team;
use App\Entity\Game;
use App\Entity\Score;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $tournament = new Tournament();
        $tournament->setTitle('Jack Burton Memorial Tournament');
        $tournament->setDescription('<p>Just remember what ol\' Jack Burton does when the earth quakes, the poison arrows fall from the sky, and the pillars of Heaven shake. Yeah, Jack Burton just looks that big old storm right in the eye and says, "Give me your best shot. I can take it."</p>');
        $tournament->setStartDate(new \DateTime('-2 WEEK'));
        $tournament->setEndDate(new \DateTime('+2 WEEK'));

        $games = $manager->getRepository('App\Entity\Game')->findAll();
        shuffle($games);
        $games = array_slice($games, 0, 12);
        foreach($games as $game) {
            $tournament->addGame($game);
        }
        $manager->persist($tournament);
        $manager->flush();

        $users = $manager->getRepository('App\Entity\User')->findAll();
        $team_size = 8;

        $names = ['The Iron Androids', 'The Powerful Turkeys', 'The Giant Phantoms', 'The Handsome Hyenas'];

        foreach($names as $i => $name) {
            $team = new Team();
            $team->setName($name);
            $members = array_slice($users, $i * $team_size, $team_size);
            foreach($members as $member) {
                $team->addMember($member);
            }
            $tournament->addTeam($team);
            $manager->persist($tournament);
        }
        $manager->flush();

        foreach($tournament->getTeams() as $team) {
            foreach($team->getMembers() as $user) {
                foreach($tournament->getGames() as $game) {
                    $points = random_int(100, 300);
                    $score = new Score($game, $tournament, $user, $team);
                    $score->setPoints($points);
                    $score->setProof('https://twitch.tv');
                    $manager->persist($score);
                }
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
            MameFixtures::class,
        );
    }
}
