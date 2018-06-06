<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180605165431 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE card_subtype (card_id INT NOT NULL, subtype_id INT NOT NULL, INDEX IDX_271AFD44ACC9A20 (card_id), INDEX IDX_271AFD48E2E245C (subtype_id), PRIMARY KEY(card_id, subtype_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE card_subtype ADD CONSTRAINT FK_271AFD44ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE card_subtype ADD CONSTRAINT FK_271AFD48E2E245C FOREIGN KEY (subtype_id) REFERENCES subtype (id)');

        // before deleting subtype_id column, migrate to card_subtype table
        $this->addSql('INSERT INTO card_subtype (card_id, subtype_id) SELECT id, subtype_id FROM card WHERE subtype_id IS NOT NULL');

        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D38E2E245C');
        $this->addSql('DROP INDEX IDX_161498D38E2E245C ON card');
        $this->addSql('ALTER TABLE card DROP subtype_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE card ADD subtype_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D38E2E245C FOREIGN KEY (subtype_id) REFERENCES subtype (id)');
        $this->addSql('CREATE INDEX IDX_161498D38E2E245C ON card (subtype_id)');

        $this->addSql('UPDATE card SET subtype_id=(SELECT subtype_id FROM card_subtype WHERE card_id=id)');

        $this->addSql('DROP TABLE card_subtype');
    }
}
