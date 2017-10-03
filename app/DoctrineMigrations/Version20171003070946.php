<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171003070946 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ext_translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, object_class VARCHAR(255) NOT NULL, field VARCHAR(32) NOT NULL, foreign_key VARCHAR(64) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX translations_lookup_idx (locale, object_class, foreign_key), UNIQUE INDEX lookup_unique_idx (locale, object_class, field, foreign_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth2_access_token (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_454D96735F37A13B (token), INDEX IDX_454D967319EB6921 (client_id), INDEX IDX_454D9673A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE affiliation (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(1024) NOT NULL, is_primary TINYINT(1) NOT NULL, UNIQUE INDEX affiliation_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth2_auth_code (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, redirect_uri LONGTEXT NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1D2905B55F37A13B (token), INDEX IDX_1D2905B519EB6921 (client_id), INDEX IDX_1D2905B5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, set_id INT DEFAULT NULL, type_id INT DEFAULT NULL, subtype_id INT DEFAULT NULL, faction_id INT DEFAULT NULL, affiliation_id INT DEFAULT NULL, rarity_id INT DEFAULT NULL, reprint_of INT DEFAULT NULL, position SMALLINT NOT NULL, code VARCHAR(255) NOT NULL, ttscardid VARCHAR(255) DEFAULT NULL, name VARCHAR(50) NOT NULL, subtitle VARCHAR(50) DEFAULT NULL, cost SMALLINT DEFAULT NULL, health SMALLINT DEFAULT NULL, points VARCHAR(20) DEFAULT NULL, text LONGTEXT DEFAULT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, deck_limit SMALLINT DEFAULT NULL, flavor LONGTEXT DEFAULT NULL, illustrator VARCHAR(255) DEFAULT NULL, is_unique TINYINT(1) NOT NULL, has_die TINYINT(1) NOT NULL, has_errata TINYINT(1) NOT NULL, INDEX IDX_161498D310FB0D18 (set_id), INDEX IDX_161498D3C54C8C93 (type_id), INDEX IDX_161498D38E2E245C (subtype_id), INDEX IDX_161498D34448F8DA (faction_id), INDEX IDX_161498D3CB94D64E (affiliation_id), INDEX IDX_161498D3F3747573 (rarity_id), INDEX IDX_161498D31C95D357 (reprint_of), INDEX card_name_idx (name), UNIQUE INDEX card_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth2_client (id INT AUTO_INCREMENT NOT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', secret VARCHAR(255) NOT NULL, allowed_grant_types LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collection (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_FC4D6532A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collectionslot (id INT AUTO_INCREMENT NOT NULL, collection_id INT DEFAULT NULL, card_id INT DEFAULT NULL, quantity SMALLINT NOT NULL, dice SMALLINT NOT NULL, INDEX IDX_49EC96F9514956FD (collection_id), INDEX IDX_49EC96F94ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, decklist_id INT DEFAULT NULL, text LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, is_hidden TINYINT(1) NOT NULL, INDEX IDX_9474526CA76ED395 (user_id), INDEX IDX_9474526CF4E9531B (decklist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deck (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, affiliation_id INT DEFAULT NULL, last_set_id INT DEFAULT NULL, parent_decklist_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, description_md LONGTEXT DEFAULT NULL, problem VARCHAR(255) DEFAULT NULL, tags VARCHAR(4000) DEFAULT NULL, major_version INT NOT NULL, minor_version INT NOT NULL, INDEX IDX_4FAC3637A76ED395 (user_id), INDEX IDX_4FAC3637CB94D64E (affiliation_id), INDEX IDX_4FAC36379AFC8FC2 (last_set_id), INDEX IDX_4FAC36379FC5416B (parent_decklist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deckchange (id INT AUTO_INCREMENT NOT NULL, deck_id INT DEFAULT NULL, date_creation DATETIME NOT NULL, variation VARCHAR(1024) NOT NULL, is_saved TINYINT(1) NOT NULL, version VARCHAR(8) DEFAULT NULL, INDEX IDX_B32E853111948DC (deck_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE decklist (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, affiliation_id INT DEFAULT NULL, last_set_id INT DEFAULT NULL, parent_deck_id INT DEFAULT NULL, precedent_decklist_id INT DEFAULT NULL, predominant_faction_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, name_canonical VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, description_md LONGTEXT DEFAULT NULL, description_html LONGTEXT DEFAULT NULL, signature VARCHAR(32) NOT NULL, nb_votes INT NOT NULL, nb_favorites INT NOT NULL, nb_comments INT NOT NULL, version VARCHAR(8) NOT NULL, INDEX IDX_ED030EC6A76ED395 (user_id), INDEX IDX_ED030EC6CB94D64E (affiliation_id), INDEX IDX_ED030EC69AFC8FC2 (last_set_id), INDEX IDX_ED030EC663513C9A (parent_deck_id), INDEX IDX_ED030EC6C386FA95 (precedent_decklist_id), INDEX IDX_ED030EC6BCFB7B5E (predominant_faction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE favorite (decklist_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_68C58ED9F4E9531B (decklist_id), INDEX IDX_68C58ED9A76ED395 (user_id), PRIMARY KEY(decklist_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote (decklist_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5A108564F4E9531B (decklist_id), INDEX IDX_5A108564A76ED395 (user_id), PRIMARY KEY(decklist_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE decklistslot (id INT AUTO_INCREMENT NOT NULL, decklist_id INT DEFAULT NULL, card_id INT DEFAULT NULL, quantity SMALLINT NOT NULL, dice SMALLINT NOT NULL, INDEX IDX_2071B1F4E9531B (decklist_id), INDEX IDX_2071B14ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deckslot (id INT AUTO_INCREMENT NOT NULL, deck_id INT DEFAULT NULL, card_id INT DEFAULT NULL, quantity SMALLINT NOT NULL, dice SMALLINT NOT NULL, INDEX IDX_5C5D6B9111948DC (deck_id), INDEX IDX_5C5D6B94ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE faction (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(1024) NOT NULL, is_primary TINYINT(1) NOT NULL, UNIQUE INDEX faction_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rarity (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(1024) NOT NULL, UNIQUE INDEX rarity_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth2_refresh_token (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_4DD907325F37A13B (token), INDEX IDX_4DD9073219EB6921 (client_id), INDEX IDX_4DD90732A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, card_id INT DEFAULT NULL, user_id INT DEFAULT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, text_md LONGTEXT NOT NULL, text_html LONGTEXT NOT NULL, nb_votes SMALLINT NOT NULL, INDEX IDX_794381C64ACC9A20 (card_id), INDEX IDX_794381C6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reviewvote (review_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_1B4C90573E2E969B (review_id), INDEX IDX_1B4C9057A76ED395 (user_id), PRIMARY KEY(review_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reviewcomment (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, review_id INT DEFAULT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, text LONGTEXT NOT NULL, INDEX IDX_E731F22FA76ED395 (user_id), INDEX IDX_E731F22F3E2E969B (review_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cardset (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(1024) NOT NULL, position SMALLINT NOT NULL, size SMALLINT DEFAULT NULL, cgdb_id_start SMALLINT DEFAULT NULL, cgdb_id_end SMALLINT DEFAULT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, date_release DATE DEFAULT NULL, UNIQUE INDEX pack_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE side (id INT AUTO_INCREMENT NOT NULL, card_id INT DEFAULT NULL, type_id INT DEFAULT NULL, value SMALLINT NOT NULL, modifier SMALLINT DEFAULT NULL, cost SMALLINT DEFAULT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, INDEX IDX_23811BB54ACC9A20 (card_id), INDEX IDX_23811BB5C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sidetype (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(15) NOT NULL, icon VARCHAR(15) NOT NULL, name VARCHAR(1024) NOT NULL, UNIQUE INDEX sidetype_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE starterpack (id INT AUTO_INCREMENT NOT NULL, set_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(50) NOT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, INDEX IDX_231EFBB710FB0D18 (set_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE starterpackslot (id INT AUTO_INCREMENT NOT NULL, starterpack_id INT DEFAULT NULL, card_id INT DEFAULT NULL, quantity SMALLINT NOT NULL, dice SMALLINT NOT NULL, INDEX IDX_E31C84D8AB172893 (starterpack_id), INDEX IDX_E31C84D84ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subtype (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(1024) NOT NULL, UNIQUE INDEX subtype_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(1024) NOT NULL, UNIQUE INDEX type_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, reputation INT NOT NULL, resume LONGTEXT DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, donation INT NOT NULL, notif_locale VARCHAR(255) DEFAULT \'en\' NOT NULL, is_notif_author TINYINT(1) DEFAULT \'1\' NOT NULL, is_notif_commenter TINYINT(1) DEFAULT \'1\' NOT NULL, is_notif_mention TINYINT(1) DEFAULT \'1\' NOT NULL, is_notif_follow TINYINT(1) DEFAULT \'1\' NOT NULL, is_notif_successor TINYINT(1) DEFAULT \'1\' NOT NULL, is_share_decks TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE follow (following_id INT NOT NULL, follower_id INT NOT NULL, INDEX IDX_683444701816E3A3 (following_id), INDEX IDX_68344470AC24F853 (follower_id), PRIMARY KEY(following_id, follower_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE oauth2_access_token ADD CONSTRAINT FK_454D967319EB6921 FOREIGN KEY (client_id) REFERENCES oauth2_client (id)');
        $this->addSql('ALTER TABLE oauth2_access_token ADD CONSTRAINT FK_454D9673A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE oauth2_auth_code ADD CONSTRAINT FK_1D2905B519EB6921 FOREIGN KEY (client_id) REFERENCES oauth2_client (id)');
        $this->addSql('ALTER TABLE oauth2_auth_code ADD CONSTRAINT FK_1D2905B5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D310FB0D18 FOREIGN KEY (set_id) REFERENCES cardset (id)');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D3C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D38E2E245C FOREIGN KEY (subtype_id) REFERENCES subtype (id)');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D34448F8DA FOREIGN KEY (faction_id) REFERENCES faction (id)');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D3CB94D64E FOREIGN KEY (affiliation_id) REFERENCES affiliation (id)');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D3F3747573 FOREIGN KEY (rarity_id) REFERENCES rarity (id)');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D31C95D357 FOREIGN KEY (reprint_of) REFERENCES card (id)');
        $this->addSql('ALTER TABLE collection ADD CONSTRAINT FK_FC4D6532A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE collectionslot ADD CONSTRAINT FK_49EC96F9514956FD FOREIGN KEY (collection_id) REFERENCES collection (id)');
        $this->addSql('ALTER TABLE collectionslot ADD CONSTRAINT FK_49EC96F94ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF4E9531B FOREIGN KEY (decklist_id) REFERENCES decklist (id)');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC3637A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC3637CB94D64E FOREIGN KEY (affiliation_id) REFERENCES affiliation (id)');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC36379AFC8FC2 FOREIGN KEY (last_set_id) REFERENCES cardset (id)');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC36379FC5416B FOREIGN KEY (parent_decklist_id) REFERENCES decklist (id)');
        $this->addSql('ALTER TABLE deckchange ADD CONSTRAINT FK_B32E853111948DC FOREIGN KEY (deck_id) REFERENCES deck (id)');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC6CB94D64E FOREIGN KEY (affiliation_id) REFERENCES affiliation (id)');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC69AFC8FC2 FOREIGN KEY (last_set_id) REFERENCES cardset (id)');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC663513C9A FOREIGN KEY (parent_deck_id) REFERENCES deck (id)');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC6C386FA95 FOREIGN KEY (precedent_decklist_id) REFERENCES decklist (id)');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC6BCFB7B5E FOREIGN KEY (predominant_faction_id) REFERENCES faction (id)');
        $this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9F4E9531B FOREIGN KEY (decklist_id) REFERENCES decklist (id)');
        $this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564F4E9531B FOREIGN KEY (decklist_id) REFERENCES decklist (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE decklistslot ADD CONSTRAINT FK_2071B1F4E9531B FOREIGN KEY (decklist_id) REFERENCES decklist (id)');
        $this->addSql('ALTER TABLE decklistslot ADD CONSTRAINT FK_2071B14ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE deckslot ADD CONSTRAINT FK_5C5D6B9111948DC FOREIGN KEY (deck_id) REFERENCES deck (id)');
        $this->addSql('ALTER TABLE deckslot ADD CONSTRAINT FK_5C5D6B94ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE oauth2_refresh_token ADD CONSTRAINT FK_4DD9073219EB6921 FOREIGN KEY (client_id) REFERENCES oauth2_client (id)');
        $this->addSql('ALTER TABLE oauth2_refresh_token ADD CONSTRAINT FK_4DD90732A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C64ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reviewvote ADD CONSTRAINT FK_1B4C90573E2E969B FOREIGN KEY (review_id) REFERENCES review (id)');
        $this->addSql('ALTER TABLE reviewvote ADD CONSTRAINT FK_1B4C9057A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reviewcomment ADD CONSTRAINT FK_E731F22FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reviewcomment ADD CONSTRAINT FK_E731F22F3E2E969B FOREIGN KEY (review_id) REFERENCES review (id)');
        $this->addSql('ALTER TABLE side ADD CONSTRAINT FK_23811BB54ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE side ADD CONSTRAINT FK_23811BB5C54C8C93 FOREIGN KEY (type_id) REFERENCES sidetype (id)');
        $this->addSql('ALTER TABLE starterpack ADD CONSTRAINT FK_231EFBB710FB0D18 FOREIGN KEY (set_id) REFERENCES cardset (id)');
        $this->addSql('ALTER TABLE starterpackslot ADD CONSTRAINT FK_E31C84D8AB172893 FOREIGN KEY (starterpack_id) REFERENCES starterpack (id)');
        $this->addSql('ALTER TABLE starterpackslot ADD CONSTRAINT FK_E31C84D84ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_683444701816E3A3 FOREIGN KEY (following_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_68344470AC24F853 FOREIGN KEY (follower_id) REFERENCES user (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D3CB94D64E');
        $this->addSql('ALTER TABLE deck DROP FOREIGN KEY FK_4FAC3637CB94D64E');
        $this->addSql('ALTER TABLE decklist DROP FOREIGN KEY FK_ED030EC6CB94D64E');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D31C95D357');
        $this->addSql('ALTER TABLE collectionslot DROP FOREIGN KEY FK_49EC96F94ACC9A20');
        $this->addSql('ALTER TABLE decklistslot DROP FOREIGN KEY FK_2071B14ACC9A20');
        $this->addSql('ALTER TABLE deckslot DROP FOREIGN KEY FK_5C5D6B94ACC9A20');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C64ACC9A20');
        $this->addSql('ALTER TABLE side DROP FOREIGN KEY FK_23811BB54ACC9A20');
        $this->addSql('ALTER TABLE starterpackslot DROP FOREIGN KEY FK_E31C84D84ACC9A20');
        $this->addSql('ALTER TABLE oauth2_access_token DROP FOREIGN KEY FK_454D967319EB6921');
        $this->addSql('ALTER TABLE oauth2_auth_code DROP FOREIGN KEY FK_1D2905B519EB6921');
        $this->addSql('ALTER TABLE oauth2_refresh_token DROP FOREIGN KEY FK_4DD9073219EB6921');
        $this->addSql('ALTER TABLE collectionslot DROP FOREIGN KEY FK_49EC96F9514956FD');
        $this->addSql('ALTER TABLE deckchange DROP FOREIGN KEY FK_B32E853111948DC');
        $this->addSql('ALTER TABLE decklist DROP FOREIGN KEY FK_ED030EC663513C9A');
        $this->addSql('ALTER TABLE deckslot DROP FOREIGN KEY FK_5C5D6B9111948DC');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF4E9531B');
        $this->addSql('ALTER TABLE deck DROP FOREIGN KEY FK_4FAC36379FC5416B');
        $this->addSql('ALTER TABLE decklist DROP FOREIGN KEY FK_ED030EC6C386FA95');
        $this->addSql('ALTER TABLE favorite DROP FOREIGN KEY FK_68C58ED9F4E9531B');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564F4E9531B');
        $this->addSql('ALTER TABLE decklistslot DROP FOREIGN KEY FK_2071B1F4E9531B');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D34448F8DA');
        $this->addSql('ALTER TABLE decklist DROP FOREIGN KEY FK_ED030EC6BCFB7B5E');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D3F3747573');
        $this->addSql('ALTER TABLE reviewvote DROP FOREIGN KEY FK_1B4C90573E2E969B');
        $this->addSql('ALTER TABLE reviewcomment DROP FOREIGN KEY FK_E731F22F3E2E969B');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D310FB0D18');
        $this->addSql('ALTER TABLE deck DROP FOREIGN KEY FK_4FAC36379AFC8FC2');
        $this->addSql('ALTER TABLE decklist DROP FOREIGN KEY FK_ED030EC69AFC8FC2');
        $this->addSql('ALTER TABLE starterpack DROP FOREIGN KEY FK_231EFBB710FB0D18');
        $this->addSql('ALTER TABLE side DROP FOREIGN KEY FK_23811BB5C54C8C93');
        $this->addSql('ALTER TABLE starterpackslot DROP FOREIGN KEY FK_E31C84D8AB172893');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D38E2E245C');
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D3C54C8C93');
        $this->addSql('ALTER TABLE oauth2_access_token DROP FOREIGN KEY FK_454D9673A76ED395');
        $this->addSql('ALTER TABLE oauth2_auth_code DROP FOREIGN KEY FK_1D2905B5A76ED395');
        $this->addSql('ALTER TABLE collection DROP FOREIGN KEY FK_FC4D6532A76ED395');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE deck DROP FOREIGN KEY FK_4FAC3637A76ED395');
        $this->addSql('ALTER TABLE decklist DROP FOREIGN KEY FK_ED030EC6A76ED395');
        $this->addSql('ALTER TABLE favorite DROP FOREIGN KEY FK_68C58ED9A76ED395');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564A76ED395');
        $this->addSql('ALTER TABLE oauth2_refresh_token DROP FOREIGN KEY FK_4DD90732A76ED395');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE reviewvote DROP FOREIGN KEY FK_1B4C9057A76ED395');
        $this->addSql('ALTER TABLE reviewcomment DROP FOREIGN KEY FK_E731F22FA76ED395');
        $this->addSql('ALTER TABLE follow DROP FOREIGN KEY FK_683444701816E3A3');
        $this->addSql('ALTER TABLE follow DROP FOREIGN KEY FK_68344470AC24F853');
        $this->addSql('DROP TABLE ext_translations');
        $this->addSql('DROP TABLE oauth2_access_token');
        $this->addSql('DROP TABLE affiliation');
        $this->addSql('DROP TABLE oauth2_auth_code');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE oauth2_client');
        $this->addSql('DROP TABLE collection');
        $this->addSql('DROP TABLE collectionslot');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE deck');
        $this->addSql('DROP TABLE deckchange');
        $this->addSql('DROP TABLE decklist');
        $this->addSql('DROP TABLE favorite');
        $this->addSql('DROP TABLE vote');
        $this->addSql('DROP TABLE decklistslot');
        $this->addSql('DROP TABLE deckslot');
        $this->addSql('DROP TABLE faction');
        $this->addSql('DROP TABLE rarity');
        $this->addSql('DROP TABLE oauth2_refresh_token');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE reviewvote');
        $this->addSql('DROP TABLE reviewcomment');
        $this->addSql('DROP TABLE cardset');
        $this->addSql('DROP TABLE side');
        $this->addSql('DROP TABLE sidetype');
        $this->addSql('DROP TABLE starterpack');
        $this->addSql('DROP TABLE starterpackslot');
        $this->addSql('DROP TABLE subtype');
        $this->addSql('DROP TABLE type');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE follow');
    }
}
