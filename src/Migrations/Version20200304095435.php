<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use App\Entity\DraftEntry;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200304095435 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getDescription() : string
    {
        return 'Create and timestamp existing draft entries.';
    }

    public function up(Schema $schema) : void
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $drafts = $em->getRepository('App\Entity\Draft')->findAll();

        foreach($drafts as $draft)
        {
            foreach($draft->getEntries() as $user) {
                $entry = new DraftEntry($draft, $user);
                $draft->addDraftEntry($entry);
            }
        }
        $em->flush();
    }

    public function down(Schema $schema) : void
    {

    }
}
