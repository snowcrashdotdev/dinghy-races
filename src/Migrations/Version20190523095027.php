<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use App\Entity\Profile;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190523095027 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getDescription() : string
    {
        return 'Assign existing Users a profile';
    }

    public function up(Schema $schema) : void
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $users = $em->getRepository('App\Entity\User')->findAll();

        foreach($users as $user)
        {
            $profile = new Profile();
            $profile->setUser($user);
        }
        $em->flush();

    }

    public function down(Schema $schema) : void
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $profiles = $em->getRepository(Profile::class)->findAll();

        foreach($profiles as $profile) {
            $profile->getUser()->setProfile(null);
            $em->remove($profile);
        }
        $em->flush();
    }
}