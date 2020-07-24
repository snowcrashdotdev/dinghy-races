<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200724090622 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add TournamentUser entity.';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tournament_user (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, tournament_id INT DEFAULT NULL, team_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, INDEX IDX_BA1E6477A76ED395 (user_id), INDEX IDX_BA1E647733D1A3E7 (tournament_id), INDEX IDX_BA1E6477296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tournament_user ADD CONSTRAINT FK_BA1E6477A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tournament_user ADD CONSTRAINT FK_BA1E647733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE tournament_user ADD CONSTRAINT FK_BA1E6477296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE score ADD tournament_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751CC61289F FOREIGN KEY (tournament_user_id) REFERENCES tournament_user (id)');
        $this->addSql('CREATE INDEX IDX_32993751CC61289F ON score (tournament_user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751CC61289F');
        $this->addSql('DROP TABLE tournament_user');
        $this->addSql('DROP INDEX IDX_32993751CC61289F ON score');
        $this->addSql('ALTER TABLE score DROP tournament_user_id');
    }
}
