<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418132508 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE draft (id INT AUTO_INCREMENT NOT NULL, tournament_id INT DEFAULT NULL, invite_token CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', UNIQUE INDEX UNIQ_467C96945242FFC4 (invite_token), UNIQUE INDEX UNIQ_467C969433D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE draft_entry (id INT AUTO_INCREMENT NOT NULL, draft_id INT NOT NULL, user_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, eligible TINYINT(1) NOT NULL, INDEX IDX_2C56195FE2F3C5D1 (draft_id), UNIQUE INDEX UNIQ_2C56195FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, year VARCHAR(255) DEFAULT NULL, manufacturer VARCHAR(255) DEFAULT NULL, marquee VARCHAR(255) DEFAULT NULL, rules LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_232B318C5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile (id INT AUTO_INCREMENT NOT NULL, social VARCHAR(255) DEFAULT NULL, picture VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE score (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, tournament_id INT DEFAULT NULL, team_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, points BIGINT NOT NULL, points_history LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', video_url VARCHAR(255) DEFAULT NULL, screenshot VARCHAR(255) DEFAULT NULL, replay VARCHAR(255) DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, rank INT DEFAULT NULL, ranked_points INT DEFAULT NULL, team_points INT DEFAULT NULL, auto_assigned TINYINT(1) DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_32993751E48FD905 (game_id), INDEX IDX_3299375133D1A3E7 (tournament_id), INDEX IDX_32993751296CD8AE (team_id), INDEX IDX_32993751A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, tournament_id INT NOT NULL, name VARCHAR(255) NOT NULL, points INT DEFAULT NULL, INDEX IDX_C4E0A61F33D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE test (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, format VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament_game (tournament_id INT NOT NULL, game_id INT NOT NULL, INDEX IDX_14A683B233D1A3E7 (tournament_id), INDEX IDX_14A683B2E48FD905 (game_id), PRIMARY KEY(tournament_id, game_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament_scoring (id INT AUTO_INCREMENT NOT NULL, tournament_id INT DEFAULT NULL, points_table LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', deadline DATE DEFAULT NULL, cutoff INT DEFAULT NULL, cutoff_score INT DEFAULT NULL, noshow_score INT DEFAULT NULL, UNIQUE INDEX UNIQ_107959C033D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament_user (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, tournament_id INT DEFAULT NULL, team_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, ranked_points INT DEFAULT NULL, team_points INT DEFAULT NULL, avg_rank DOUBLE PRECISION DEFAULT NULL, completion DOUBLE PRECISION DEFAULT NULL, INDEX IDX_BA1E6477A76ED395 (user_id), INDEX IDX_BA1E647733D1A3E7 (tournament_id), INDEX IDX_BA1E6477296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, profile_id INT DEFAULT NULL, created_at DATETIME NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, verified TINYINT(1) NOT NULL, reset_token VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649CCFA12B8 (profile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE draft ADD CONSTRAINT FK_467C969433D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE draft_entry ADD CONSTRAINT FK_2C56195FE2F3C5D1 FOREIGN KEY (draft_id) REFERENCES draft (id)');
        $this->addSql('ALTER TABLE draft_entry ADD CONSTRAINT FK_2C56195FA76ED395 FOREIGN KEY (user_id) REFERENCES tournament_user (id)');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_3299375133D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F33D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE tournament_game ADD CONSTRAINT FK_14A683B233D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournament_game ADD CONSTRAINT FK_14A683B2E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournament_scoring ADD CONSTRAINT FK_107959C033D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE tournament_user ADD CONSTRAINT FK_BA1E6477A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tournament_user ADD CONSTRAINT FK_BA1E647733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE tournament_user ADD CONSTRAINT FK_BA1E6477296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE draft_entry DROP FOREIGN KEY FK_2C56195FE2F3C5D1');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751E48FD905');
        $this->addSql('ALTER TABLE tournament_game DROP FOREIGN KEY FK_14A683B2E48FD905');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649CCFA12B8');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751296CD8AE');
        $this->addSql('ALTER TABLE tournament_user DROP FOREIGN KEY FK_BA1E6477296CD8AE');
        $this->addSql('ALTER TABLE draft DROP FOREIGN KEY FK_467C969433D1A3E7');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_3299375133D1A3E7');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F33D1A3E7');
        $this->addSql('ALTER TABLE tournament_game DROP FOREIGN KEY FK_14A683B233D1A3E7');
        $this->addSql('ALTER TABLE tournament_scoring DROP FOREIGN KEY FK_107959C033D1A3E7');
        $this->addSql('ALTER TABLE tournament_user DROP FOREIGN KEY FK_BA1E647733D1A3E7');
        $this->addSql('ALTER TABLE draft_entry DROP FOREIGN KEY FK_2C56195FA76ED395');
        $this->addSql('ALTER TABLE tournament_user DROP FOREIGN KEY FK_BA1E6477A76ED395');
        $this->addSql('DROP TABLE draft');
        $this->addSql('DROP TABLE draft_entry');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE score');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE test');
        $this->addSql('DROP TABLE tournament');
        $this->addSql('DROP TABLE tournament_game');
        $this->addSql('DROP TABLE tournament_scoring');
        $this->addSql('DROP TABLE tournament_user');
        $this->addSql('DROP TABLE user');
    }
}
