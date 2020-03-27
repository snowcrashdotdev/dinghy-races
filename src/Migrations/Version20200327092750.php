<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200327092750 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add TournamentScoring entity';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tournament_scoring (id INT AUTO_INCREMENT NOT NULL, points_table LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', cutoff_date DATE DEFAULT NULL, cutoff_line INT DEFAULT NULL, cutoff_score INT DEFAULT NULL, noshow_score INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tournament ADD scoring_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D9DF2EDCBF FOREIGN KEY (scoring_id) REFERENCES tournament_scoring (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD5FB8D9DF2EDCBF ON tournament (scoring_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tournament DROP FOREIGN KEY FK_BD5FB8D9DF2EDCBF');
        $this->addSql('DROP TABLE tournament_scoring');
        $this->addSql('DROP INDEX UNIQ_BD5FB8D9DF2EDCBF ON tournament');
        $this->addSql('ALTER TABLE tournament DROP scoring_id');
    }
}
