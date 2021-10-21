<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210301202844 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE provider (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(125) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product ADD provider_id INT DEFAULT NULL, ADD bill_number VARCHAR(35) DEFAULT NULL, ADD code VARCHAR(35) DEFAULT NULL, ADD mark VARCHAR(125) DEFAULT NULL, ADD purchace_price_un_ht DOUBLE PRECISION NOT NULL, ADD tva SMALLINT NOT NULL, ADD gain SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id)');
        $this->addSql('CREATE INDEX IDX_D34A04ADA53A8AA ON product (provider_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADA53A8AA');
        $this->addSql('DROP TABLE provider');
        $this->addSql('DROP INDEX IDX_D34A04ADA53A8AA ON product');
        $this->addSql('ALTER TABLE product DROP provider_id, DROP bill_number, DROP code, DROP mark, DROP purchace_price_un_ht, DROP tva, DROP gain');
    }
}
