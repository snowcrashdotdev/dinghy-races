<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200401141607 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tournament CHANGE description description LONGTEXT DEFAULT NULL, CHANGE start_date start_date DATE DEFAULT NULL, CHANGE end_date end_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE tournament_scoring CHANGE cutoff_date deadline DATE DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tournament CHANGE description description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE start_date start_date DATE NOT NULL, CHANGE end_date end_date DATE NOT NULL');
        $this->addSql('ALTER TABLE tournament_scoring CHANGE deadline cutoff_date DATE DEFAULT NULL');
    }
}
