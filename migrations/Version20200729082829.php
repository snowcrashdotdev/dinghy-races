<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200729082829 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create TournamentUsers from existing tables.';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        /**
         * Create a tournament user for each user signed up for an
         * INDIVIDUAL format tournament
         */
        $this->addSql('INSERT INTO tournament_user (created_at, user_id, tournament_id) SELECT NOW(), user_id, tournament_id FROM user_tournament INNER JOIN tournament ON user_tournament.tournament_id = tournament.id WHERE tournament.format = \'INDIVIDUAL\'');

        /**
         * Create a tournament user for each existing team member.
         */
        $this->addSql('INSERT INTO tournament_user (created_at, user_id, team_id, tournament_id) SELECT NOW(), u.user_id, u.team_id, t.tournament_id FROM team_user u INNER JOIN team t ON u.team_id = t.id');

        /**
         * Assign tournament scores to newly created tournament_user records.
         */
        $this->addSql('UPDATE score s INNER JOIN tournament_user u ON s.user_id = u.user_id SET s.tournament_user_id = u.id WHERE s.tournament_id = u.tournament_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
