<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200730090724 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add fields for tournament stats and update existing records.';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tournament_user ADD avg_rank DOUBLE PRECISION DEFAULT NULL, ADD completion DOUBLE PRECISION DEFAULT NULL');

        $this->addSql('
            UPDATE tournament_user AS u,
            (
                SELECT
                    s.user_id as user_id,
                    AVG(s.rank) AS avg_rank,
                    SUM(s.ranked_points) AS ranked_points,
                    SUM(s.team_points) AS team_points,
                    SUM(CASE WHEN s.auto_assigned = 1 THEN 0 ELSE 1 END) AS submissions
                    FROM score s
                    WHERE s.type = \'tournament\'
                    GROUP BY s.user_id
            ) AS stats
            SET
            u.avg_rank = stats.avg_rank,
            u.ranked_points = stats.ranked_points,
            u.team_points = stats.team_points,
            u.avg_rank = stats.avg_rank,
            u.completion = stats.submissions
            WHERE u.id = stats.user_id
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tournament_user DROP avg_rank, DROP completion');
    }
}
