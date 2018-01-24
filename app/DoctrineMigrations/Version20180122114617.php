<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180122114617 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE cycle (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(1024) NOT NULL, position SMALLINT NOT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, date_release DATE DEFAULT NULL, UNIQUE INDEX cycle_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE format (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, UNIQUE INDEX format_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE deck ADD format_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC3637D629F605 FOREIGN KEY (format_id) REFERENCES format (id)');
        $this->addSql('CREATE INDEX IDX_4FAC3637D629F605 ON deck (format_id)');
        $this->addSql('ALTER TABLE decklist ADD format_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC6D629F605 FOREIGN KEY (format_id) REFERENCES format (id)');
        $this->addSql('CREATE INDEX IDX_ED030EC6D629F605 ON decklist (format_id)');
        $this->addSql('ALTER TABLE cardset ADD cycle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cardset ADD CONSTRAINT FK_CA997ACC5EC1162 FOREIGN KEY (cycle_id) REFERENCES cycle (id)');
        $this->addSql('CREATE INDEX IDX_CA997ACC5EC1162 ON cardset (cycle_id)');

        $this->addSql("INSERT INTO format (code, name, data, date_creation, date_update) VALUES ('STD', 'Standard', '{}', NOW(), NOW())");
        $this->addSql("INSERT INTO format (code, name, data, date_creation, date_update) VALUES ('TRI', 'Trilogy', '{}', NOW(), NOW())");
        $this->addSql("INSERT INTO format (code, name, data, date_creation, date_update) VALUES ('INF', 'Infinite', '{}', NOW(), NOW())");

        $this->addSql("UPDATE deck SET format_id=(SELECT id FROM format WHERE code='STD')");
        $this->addSql("UPDATE decklist SET format_id=(SELECT id FROM format WHERE code='STD')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cardset DROP FOREIGN KEY FK_CA997ACC5EC1162');
        $this->addSql('ALTER TABLE deck DROP FOREIGN KEY FK_4FAC3637D629F605');
        $this->addSql('ALTER TABLE decklist DROP FOREIGN KEY FK_ED030EC6D629F605');
        $this->addSql('DROP TABLE cycle');
        $this->addSql('DROP TABLE format');
        $this->addSql('DROP INDEX IDX_CA997ACC5EC1162 ON cardset');
        $this->addSql('ALTER TABLE cardset DROP cycle_id');
        $this->addSql('DROP INDEX IDX_4FAC3637D629F605 ON deck');
        $this->addSql('ALTER TABLE deck DROP format_id');
        $this->addSql('DROP INDEX IDX_ED030EC6D629F605 ON decklist');
        $this->addSql('ALTER TABLE decklist DROP format_id');
    }
}
