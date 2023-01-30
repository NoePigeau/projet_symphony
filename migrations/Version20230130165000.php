<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230130165000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE equipment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "order_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE rating_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE step_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE equipment (id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, stock INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "order" (id INT NOT NULL, agent_id INT NOT NULL, mission_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F52993983414710B ON "order" (agent_id)');
        $this->addSql('CREATE INDEX IDX_F5299398BE6CAE90 ON "order" (mission_id)');
        $this->addSql('CREATE TABLE order_equipment (order_id INT NOT NULL, equipment_id INT NOT NULL, PRIMARY KEY(order_id, equipment_id))');
        $this->addSql('CREATE INDEX IDX_6FBFAE7B8D9F6D38 ON order_equipment (order_id)');
        $this->addSql('CREATE INDEX IDX_6FBFAE7B517FE9FE ON order_equipment (equipment_id)');
        $this->addSql('CREATE TABLE rating (id INT NOT NULL, agent_id INT NOT NULL, rate INT NOT NULL, opinion TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D88926223414710B ON rating (agent_id)');
        $this->addSql('CREATE TABLE step (id INT NOT NULL, mission_id INT NOT NULL, name VARCHAR(255) NOT NULL, status BOOLEAN NOT NULL, position INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_43B9FE3CBE6CAE90 ON step (mission_id)');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F52993983414710B FOREIGN KEY (agent_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F5299398BE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_equipment ADD CONSTRAINT FK_6FBFAE7B8D9F6D38 FOREIGN KEY (order_id) REFERENCES "order" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_equipment ADD CONSTRAINT FK_6FBFAE7B517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D88926223414710B FOREIGN KEY (agent_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE step ADD CONSTRAINT FK_43B9FE3CBE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mission_user DROP CONSTRAINT fk_a4d17a46be6cae90');
        $this->addSql('ALTER TABLE mission_user DROP CONSTRAINT fk_a4d17a46a76ed395');
        $this->addSql('DROP TABLE mission_user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE equipment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "order_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE rating_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE step_id_seq CASCADE');
        $this->addSql('CREATE TABLE mission_user (mission_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(mission_id, user_id))');
        $this->addSql('CREATE INDEX idx_a4d17a46a76ed395 ON mission_user (user_id)');
        $this->addSql('CREATE INDEX idx_a4d17a46be6cae90 ON mission_user (mission_id)');
        $this->addSql('ALTER TABLE mission_user ADD CONSTRAINT fk_a4d17a46be6cae90 FOREIGN KEY (mission_id) REFERENCES mission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mission_user ADD CONSTRAINT fk_a4d17a46a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F52993983414710B');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F5299398BE6CAE90');
        $this->addSql('ALTER TABLE order_equipment DROP CONSTRAINT FK_6FBFAE7B8D9F6D38');
        $this->addSql('ALTER TABLE order_equipment DROP CONSTRAINT FK_6FBFAE7B517FE9FE');
        $this->addSql('ALTER TABLE rating DROP CONSTRAINT FK_D88926223414710B');
        $this->addSql('ALTER TABLE step DROP CONSTRAINT FK_43B9FE3CBE6CAE90');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP TABLE order_equipment');
        $this->addSql('DROP TABLE rating');
        $this->addSql('DROP TABLE step');
    }
}
