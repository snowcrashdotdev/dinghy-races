<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190901122859 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add Draft entity with properties tournament and entries.';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE draft (id INT AUTO_INCREMENT NOT NULL, tournament_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_467C969433D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE draft_user (draft_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_97D3E04DE2F3C5D1 (draft_id), INDEX IDX_97D3E04DA76ED395 (user_id), PRIMARY KEY(draft_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE draft ADD CONSTRAINT FK_467C969433D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE draft_user ADD CONSTRAINT FK_97D3E04DE2F3C5D1 FOREIGN KEY (draft_id) REFERENCES draft (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE draft_user ADD CONSTRAINT FK_97D3E04DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE draft_user DROP FOREIGN KEY FK_97D3E04DE2F3C5D1');
        $this->addSql('DROP TABLE draft');
        $this->addSql('DROP TABLE draft_user');
    }
}
