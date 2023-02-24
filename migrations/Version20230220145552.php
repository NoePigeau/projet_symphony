<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230220145552 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}
	
	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE SEQUENCE reset_password_request_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
		$this->addSql('CREATE TABLE reset_password_request (id INT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
		$this->addSql('CREATE INDEX IDX_7CE748AA76ED395 ON reset_password_request (user_id)');
		$this->addSql('COMMENT ON COLUMN reset_password_request.requested_at IS \'(DC2Type:datetime_immutable)\'');
		$this->addSql('COMMENT ON COLUMN reset_password_request.expires_at IS \'(DC2Type:datetime_immutable)\'');
		$this->addSql('CREATE TABLE user_type (user_id INT NOT NULL, type_id INT NOT NULL, PRIMARY KEY(user_id, type_id))');
		$this->addSql('CREATE INDEX IDX_F65F1BE0A76ED395 ON user_type (user_id)');
		$this->addSql('CREATE INDEX IDX_F65F1BE0C54C8C93 ON user_type (type_id)');
		$this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
		$this->addSql('ALTER TABLE user_type ADD CONSTRAINT FK_F65F1BE0A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
		$this->addSql('ALTER TABLE user_type ADD CONSTRAINT FK_F65F1BE0C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
		$this->addSql('ALTER TABLE equipment ADD image VARCHAR(255) DEFAULT NULL');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_D338D5835E237E06 ON equipment (name)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_8CDE57295E237E06 ON type (name)');
	}
	
	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE SCHEMA public');
		$this->addSql('DROP SEQUENCE reset_password_request_id_seq CASCADE');
		$this->addSql('ALTER TABLE reset_password_request DROP CONSTRAINT FK_7CE748AA76ED395');
		$this->addSql('ALTER TABLE user_type DROP CONSTRAINT FK_F65F1BE0A76ED395');
		$this->addSql('ALTER TABLE user_type DROP CONSTRAINT FK_F65F1BE0C54C8C93');
		$this->addSql('DROP TABLE reset_password_request');
		$this->addSql('DROP TABLE user_type');
		$this->addSql('DROP INDEX UNIQ_8CDE57295E237E06');
		$this->addSql('DROP INDEX UNIQ_D338D5835E237E06');
		$this->addSql('ALTER TABLE equipment DROP image');
	}
}