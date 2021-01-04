<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20210102191213 extends AbstractMigration
{
	/**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE card ADD parallel_die INT DEFAULT NULL');
		$this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D31C95D358 FOREIGN KEY (parallel_die) REFERENCES card (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('ALTER TABLE card DROP CONSTRAINT FK_161498D31C95D358');
        $this->addSql('ALTER TABLE card DROP COLUMN parallel_die');
    }
}
