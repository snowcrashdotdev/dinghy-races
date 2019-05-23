<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190522182123 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE game CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE year year VARCHAR(255) DEFAULT NULL, CHANGE manufacturer manufacturer VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE score CHANGE video_url video_url VARCHAR(255) DEFAULT NULL, CHANGE screenshot screenshot VARCHAR(255) DEFAULT NULL, CHANGE comment comment VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE team ADD captains LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE points points INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profile CHANGE user_id user_id INT DEFAULT NULL, CHANGE profile profile JSON DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE game CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE year year VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE manufacturer manufacturer VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE profile CHANGE user_id user_id INT DEFAULT NULL, CHANGE profile profile LONGTEXT DEFAULT NULL COLLATE utf8mb4_bin');
        $this->addSql('ALTER TABLE score CHANGE video_url video_url VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE screenshot screenshot VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE comment comment VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE team DROP captains, CHANGE points points INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE utf8mb4_bin, CHANGE email email VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE reset_token reset_token VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
