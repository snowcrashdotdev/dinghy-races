<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190514143456 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, year VARCHAR(255) DEFAULT NULL, manufacturer VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_232B318C5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE score (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, game_id INT NOT NULL, tournament_id INT NOT NULL, proof VARCHAR(255) DEFAULT NULL, date_submitted DATETIME NOT NULL, date_updated DATETIME NOT NULL, points INT NOT NULL, rank INT NOT NULL, INDEX IDX_32993751A76ED395 (user_id), INDEX IDX_32993751E48FD905 (game_id), INDEX IDX_3299375133D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, tournament_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_C4E0A61F33D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team_user (team_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5C722232296CD8AE (team_id), INDEX IDX_5C722232A76ED395 (user_id), PRIMARY KEY(team_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament_game (tournament_id INT NOT NULL, game_id INT NOT NULL, INDEX IDX_14A683B233D1A3E7 (tournament_id), INDEX IDX_14A683B2E48FD905 (game_id), PRIMARY KEY(tournament_id, game_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_3299375133D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F33D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE team_user ADD CONSTRAINT FK_5C722232296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_user ADD CONSTRAINT FK_5C722232A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournament_game ADD CONSTRAINT FK_14A683B233D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tournament_game ADD CONSTRAINT FK_14A683B2E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751E48FD905');
        $this->addSql('ALTER TABLE tournament_game DROP FOREIGN KEY FK_14A683B2E48FD905');
        $this->addSql('ALTER TABLE team_user DROP FOREIGN KEY FK_5C722232296CD8AE');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_3299375133D1A3E7');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F33D1A3E7');
        $this->addSql('ALTER TABLE tournament_game DROP FOREIGN KEY FK_14A683B233D1A3E7');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751A76ED395');
        $this->addSql('ALTER TABLE team_user DROP FOREIGN KEY FK_5C722232A76ED395');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE score');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE team_user');
        $this->addSql('DROP TABLE tournament');
        $this->addSql('DROP TABLE tournament_game');
        $this->addSql('DROP TABLE user');
    }
}
