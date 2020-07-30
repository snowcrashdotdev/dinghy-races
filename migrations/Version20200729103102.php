<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200729103102 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add score totals to TournamentUsers';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        /**
         * Add columns to tournament_user to store total point values.
         */
        $this->addSql('ALTER TABLE tournament_user ADD ranked_points INT DEFAULT NULL, ADD team_points INT DEFAULT NULL');

        /**
         * Update tournament_user with existing sums.
         */
        $this->addSql('UPDATE tournament_user u, (SELECT tournament_user_id, SUM(ranked_points) AS ranked_points, SUM(team_points) AS team_points FROM score GROUP BY tournament_user_id) AS scores SET u.ranked_points = scores.ranked_points, u.team_points = scores.team_points WHERE u.id = scores.tournament_user_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tournament_user DROP ranked_points, DROP team_points');
    }
}
