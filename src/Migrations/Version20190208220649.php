<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190208220649 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE photo ADD alt VARCHAR(255) DEFAULT NULL, DROP update_at, CHANGE image_size author_id INT NOT NULL, CHANGE image_name file VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B78418F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_14B78418F675F31B ON photo (author_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B78418F675F31B');
        $this->addSql('DROP INDEX IDX_14B78418F675F31B ON photo');
        $this->addSql('ALTER TABLE photo ADD update_at DATETIME NOT NULL, DROP alt, CHANGE file image_name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE author_id image_size INT NOT NULL');
    }
}
