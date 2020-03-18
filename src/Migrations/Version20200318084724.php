<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200318084724 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add PB, points history to Score, and alter Array columns';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE personal_best (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, game_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, video_url VARCHAR(255) DEFAULT NULL, screenshot VARCHAR(255) DEFAULT NULL, comment VARCHAR(144) DEFAULT NULL, points BIGINT NOT NULL, points_history LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_9C077172A76ED395 (user_id), INDEX IDX_9C077172E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE personal_best ADD CONSTRAINT FK_9C077172A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE personal_best ADD CONSTRAINT FK_9C077172E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('DROP TABLE cache_items');
        $this->addSql('ALTER TABLE score ADD points_history LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE cache_items (item_id VARBINARY(255) NOT NULL, item_data MEDIUMBLOB NOT NULL, item_lifetime INT UNSIGNED DEFAULT NULL, item_time INT UNSIGNED NOT NULL, PRIMARY KEY(item_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE personal_best');
        $this->addSql('ALTER TABLE score DROP points_history');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
