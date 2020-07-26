<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200725124710 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add tournament user column to draft entries.';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE draft_entry ADD tournament_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE draft_entry ADD CONSTRAINT FK_2C56195FCC61289F FOREIGN KEY (tournament_user_id) REFERENCES tournament_user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2C56195FCC61289F ON draft_entry (tournament_user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE draft_entry DROP FOREIGN KEY FK_2C56195FCC61289F');
        $this->addSql('DROP INDEX UNIQ_2C56195FCC61289F ON draft_entry');
        $this->addSql('ALTER TABLE draft_entry DROP tournament_user_id');
    }
}
