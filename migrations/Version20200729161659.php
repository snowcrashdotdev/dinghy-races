<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200729161659 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Copy identifying keys in score table and drop unused tables.';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        /**
         * Copy foreign key to user column for all tournament scores.
         */
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751A76ED395');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751CC61289F');
        $this->addSql('UPDATE score SET score.user_id = score.tournament_user_id WHERE score.type = \'tournament\'');

        /**
         * Auto-generated migration to reconcile schema with
         * ORM configuration defined in each Entity.
         */
        $this->addSql('DROP TABLE team_user');
        $this->addSql('DROP TABLE user_tournament');
        $this->addSql('DROP INDEX IDX_32993751CC61289F ON score');
        $this->addSql('ALTER TABLE score DROP tournament_user_id');
        $this->addSql('DELETE FROM draft_entry');
        $this->addSql('ALTER TABLE draft_entry DROP INDEX IDX_2C56195FA76ED395, ADD UNIQUE INDEX UNIQ_2C56195FA76ED395 (user_id)');
        $this->addSql('ALTER TABLE draft_entry DROP FOREIGN KEY FK_2C56195FCC61289F');
        $this->addSql('ALTER TABLE draft_entry DROP FOREIGN KEY FK_2C56195FA76ED395');
        $this->addSql('DROP INDEX UNIQ_2C56195FCC61289F ON draft_entry');
        $this->addSql('ALTER TABLE draft_entry DROP tournament_user_id, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE draft_entry ADD CONSTRAINT FK_2C56195FA76ED395 FOREIGN KEY (user_id) REFERENCES tournament_user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE team_user (team_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5C722232296CD8AE (team_id), INDEX IDX_5C722232A76ED395 (user_id), PRIMARY KEY(team_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_tournament (user_id INT NOT NULL, tournament_id INT NOT NULL, INDEX IDX_1A387E35A76ED395 (user_id), INDEX IDX_1A387E3533D1A3E7 (tournament_id), PRIMARY KEY(user_id, tournament_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE team_user ADD CONSTRAINT FK_5C722232296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_user ADD CONSTRAINT FK_5C722232A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_tournament ADD CONSTRAINT FK_1A387E3533D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_tournament ADD CONSTRAINT FK_1A387E35A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE draft_entry DROP INDEX UNIQ_2C56195FA76ED395, ADD INDEX IDX_2C56195FA76ED395 (user_id)');
        $this->addSql('ALTER TABLE draft_entry DROP FOREIGN KEY FK_2C56195FA76ED395');
        $this->addSql('ALTER TABLE draft_entry ADD tournament_user_id INT DEFAULT NULL, CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE draft_entry ADD CONSTRAINT FK_2C56195FCC61289F FOREIGN KEY (tournament_user_id) REFERENCES tournament_user (id)');
        $this->addSql('ALTER TABLE draft_entry ADD CONSTRAINT FK_2C56195FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2C56195FCC61289F ON draft_entry (tournament_user_id)');
        $this->addSql('ALTER TABLE score ADD tournament_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751CC61289F FOREIGN KEY (tournament_user_id) REFERENCES tournament_user (id)');
        $this->addSql('CREATE INDEX IDX_32993751CC61289F ON score (tournament_user_id)');
        $this->addSql('ALTER TABLE tournament_user CHANGE ranked_points ranked_points INT DEFAULT 0 NOT NULL');
    }
}
