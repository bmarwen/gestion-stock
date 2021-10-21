<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210304095829 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bills (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(125) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, bill_pdfname VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product ADD bill_id INT DEFAULT NULL, DROP bill_number');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD1A8C12F5 FOREIGN KEY (bill_id) REFERENCES bills (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD1A8C12F5 ON product (bill_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD1A8C12F5');
        $this->addSql('DROP TABLE bills');
        $this->addSql('DROP INDEX IDX_D34A04AD1A8C12F5 ON product');
        $this->addSql('ALTER TABLE product ADD bill_number VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP bill_id');
    }
}
