<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $admin = new User();
        $admin->setUsername('admin');

        $password = $this->encoder->encodePassword($admin, 'admin');
        $admin->setPassword($password);

        $admin->addRoles(array('ROLE_ADMIN'));

        $manager->persist($admin);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['dev'];
    }
}
