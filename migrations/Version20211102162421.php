<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211102162421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE support_request_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE support_request (id INT NOT NULL, req_id VARCHAR(20) NOT NULL, from_email VARCHAR(255) NOT NULL, from_full VARCHAR(255) DEFAULT NULL, subject VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE inbox_mail ADD CONSTRAINT FK_98B922D2315B405 FOREIGN KEY (support_id) REFERENCES support_request (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_98B922D2315B405 ON inbox_mail (support_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE inbox_mail DROP CONSTRAINT FK_98B922D2315B405');
        $this->addSql('DROP SEQUENCE support_request_id_seq CASCADE');
        $this->addSql('DROP TABLE support_request');
        $this->addSql('DROP INDEX IDX_98B922D2315B405');
    }
}
