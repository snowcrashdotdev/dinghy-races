<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $admin = new User();
        $admin->setUsername('admin');

        $password = $this->encoder->encodePassword($admin, 'admin');
        $admin->setPassword($password);

        $admin->setRoles(array('ROLE_ADMIN', 'ROLE_CAPTAIN'));

        $manager->persist($admin);

        for ($i=0; $i < 31; $i++) {
            $player = new User();

            $username = 'luser_' . sprintf("%03d", $i);
            $player->setUsername($username);

            $password = $this->encoder->encodePassword($player, 'luser');
            $player->setPassword($password);

            if ($i < 7) { $player->setRoles(array('ROLE_CAPTAIN')); }
            $manager->persist($player);
        }
  
        $manager->flush();
    }
}
