<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200404093855 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Bring this db up to speed.';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tournament_scoring (id INT AUTO_INCREMENT NOT NULL, tournament_id INT DEFAULT NULL, points_table LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', deadline DATE DEFAULT NULL, cutoff INT DEFAULT NULL, cutoff_score INT DEFAULT NULL, noshow_score INT DEFAULT NULL, UNIQUE INDEX UNIQ_107959C033D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tournament_scoring ADD CONSTRAINT FK_107959C033D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('DROP TABLE draft_user');
        $this->addSql('ALTER TABLE draft_entry ADD eligible TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE game ADD marquee VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE score CHANGE date_submitted created_at DATETIME NOT NULL, CHANGE date_updated updated_at DATETIME NOT NULL, ADD points_history LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', ADD replay VARCHAR(255) DEFAULT NULL, ADD type VARCHAR(255) NOT NULL, ADD rank INT DEFAULT NULL, ADD team_points INT DEFAULT NULL, CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE team_id team_id INT DEFAULT NULL, CHANGE ranked_points ranked_points INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profile ADD picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE team DROP captains');
        $this->addSql('ALTER TABLE tournament DROP scoring_table, DROP cutoff_date, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE start_date start_date DATE DEFAULT NULL, CHANGE end_date end_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        
        $empty_array = serialize( array() );
        $this->addSql('UPDATE score SET type = \'tournament\'');
        $this->addSql('UPDATE score SET points_history = "' . $empty_array . '"'); // initialize score history
        $this->addSql('UPDATE draft_entry e JOIN draft d ON e.draft_id = d.id JOIN tournament t ON t.id = d.tournament_id SET e.eligible = 1 WHERE t.start_date > CURRENT_TIMESTAMP');
        $this->addSql('UPDATE score SET points = 0, ranked_points = 0 WHERE auto_assigned = 1');
        $this->addSql('INSERT score (user_id, game_id, created_at, updated_at, points, points_history, video_url, screenshot, comment, type) SELECT user_id, game_id, created_at, updated_at, points, "a:0:{}" as points_history, video_url, screenshot, comment, "personal_best" as type FROM calice.score WHERE points > 0');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE draft_user (draft_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_97D3E04DE2F3C5D1 (draft_id), INDEX IDX_97D3E04DA76ED395 (user_id), PRIMARY KEY(draft_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE draft_user ADD CONSTRAINT FK_97D3E04DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE draft_user ADD CONSTRAINT FK_97D3E04DE2F3C5D1 FOREIGN KEY (draft_id) REFERENCES draft (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE tournament_scoring');
        $this->addSql('ALTER TABLE draft_entry DROP eligible');
        $this->addSql('ALTER TABLE game DROP marquee');
        $this->addSql('ALTER TABLE profile DROP picture');
        $this->addSql('ALTER TABLE score ADD date_submitted DATETIME NOT NULL, ADD date_updated DATETIME NOT NULL, DROP created_at, DROP updated_at, DROP points_history, DROP replay, DROP type, DROP rank, DROP team_points, CHANGE tournament_id tournament_id INT NOT NULL, CHANGE team_id team_id INT NOT NULL, CHANGE ranked_points ranked_points BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE team ADD captains LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE tournament ADD scoring_table LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', ADD cutoff_date DATE DEFAULT NULL, CHANGE description description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE start_date start_date DATE NOT NULL, CHANGE end_date end_date DATE NOT NULL');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
