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

        $usernames = [
            'Despatche',
            'ei',
            'p',
            'viewtyjoe',
            'Aquas',
            'Dumplechan',
            'MightySquirrel',
            'owlnonymous',
            'SpryteMix',
            'blossom',
            'Erppo',
            'Fiztastic',
            'Jakazam',
            'KaizaCorp',
            'D_nir',
            'DenT4F',
            'deuce985',
            'Jaimers',
            'Cowslanlr',
            'djtatsujin',
            'goingfullschmitt',
            'Mark_MSX',
            'moglar5K',
            'Neo_Antwon',
            'ScopedPixels',
            'Sensato',
            'skipnatty',
            'V0lrat',
            'wgogh',
            'y&cow',
            'Zotmeister'
        ];

        foreach($usernames as $name) {
            $user = new User();
            $user->setUsername($name);
            $password = $this->encoder->encodePassword($user, $name);
            $user->setPassword($password);
            $manager->persist($user);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['dev'];
    }
}
