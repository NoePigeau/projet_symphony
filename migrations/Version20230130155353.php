<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230130155353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD description VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD status BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD validation_token VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD email_notify BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER nickname SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER firstname SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER lastname SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP description');
        $this->addSql('ALTER TABLE "user" DROP status');
        $this->addSql('ALTER TABLE "user" DROP validation_token');
        $this->addSql('ALTER TABLE "user" DROP email_notify');
        $this->addSql('ALTER TABLE "user" ALTER nickname DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER firstname DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER lastname DROP NOT NULL');
    }
}
