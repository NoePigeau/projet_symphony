<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230131110002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message ADD mission_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE message ADD from_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FEFD2C16A FOREIGN KEY (mission_id_id) REFERENCES mission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F4632BB48 FOREIGN KEY (from_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_B6BD307FEFD2C16A ON message (mission_id_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307F4632BB48 ON message (from_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307FEFD2C16A');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F4632BB48');
        $this->addSql('DROP INDEX IDX_B6BD307FEFD2C16A');
        $this->addSql('DROP INDEX IDX_B6BD307F4632BB48');
        $this->addSql('ALTER TABLE message DROP mission_id_id');
        $this->addSql('ALTER TABLE message DROP from_id_id');
    }
}
