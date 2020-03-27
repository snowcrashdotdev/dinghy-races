<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200327201112 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE draft_user');
        $this->addSql('ALTER TABLE score ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD points_history LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', ADD replay VARCHAR(255) DEFAULT NULL, ADD type VARCHAR(255) NOT NULL, ADD rank INT DEFAULT NULL, ADD team_points INT DEFAULT NULL, DROP date_submitted, DROP date_updated, CHANGE tournament_id tournament_id INT DEFAULT NULL, CHANGE team_id team_id INT DEFAULT NULL, CHANGE ranked_points ranked_points INT DEFAULT NULL');
        $this->addSql('ALTER TABLE team DROP captains');
        $this->addSql('ALTER TABLE game ADD marquee VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('ALTER TABLE profile ADD picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tournament DROP scoring_table, DROP cutoff_date');
        $this->addSql('UPDATE score SET type = "tournament"'); // initialize score types
        $empty_array = serialize( array() );
        $this->addSql('UPDATE score SET points_history = "' . $empty_array . '"'); // initialize score history
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE draft_user (draft_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_97D3E04DA76ED395 (user_id), INDEX IDX_97D3E04DE2F3C5D1 (draft_id), PRIMARY KEY(draft_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE draft_user ADD CONSTRAINT FK_97D3E04DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE draft_user ADD CONSTRAINT FK_97D3E04DE2F3C5D1 FOREIGN KEY (draft_id) REFERENCES draft (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game DROP marquee');
        $this->addSql('ALTER TABLE profile DROP picture');
        $this->addSql('ALTER TABLE score ADD date_submitted DATETIME NOT NULL, ADD date_updated DATETIME NOT NULL, DROP created_at, DROP updated_at, DROP points_history, DROP replay, DROP type, DROP rank, DROP team_points, CHANGE tournament_id tournament_id INT NOT NULL, CHANGE team_id team_id INT NOT NULL, CHANGE ranked_points ranked_points BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE team ADD captains LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE tournament ADD scoring_table LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', ADD cutoff_date DATE DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');     
    }
}
