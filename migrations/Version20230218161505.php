<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230218161505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment ADD mission_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD amount INT NOT NULL');
        $this->addSql('ALTER TABLE payment ADD stripe_payment_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE payment ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE payment ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE payment ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DBE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6D28840DBE6CAE90 ON payment (mission_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840DBE6CAE90');
        $this->addSql('DROP INDEX IDX_6D28840DBE6CAE90');
        $this->addSql('ALTER TABLE payment DROP mission_id');
        $this->addSql('ALTER TABLE payment DROP amount');
        $this->addSql('ALTER TABLE payment DROP stripe_payment_id');
        $this->addSql('ALTER TABLE payment DROP status');
        $this->addSql('ALTER TABLE payment DROP created_at');
        $this->addSql('ALTER TABLE payment DROP updated_at');
    }
}
