<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240219100120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customers (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_62534E21727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE employes (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, countope INT NOT NULL, INDEX IDX_A94BC0F0727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE operations (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, employe_id INT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, forfait INT NOT NULL, status INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', rdv_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', finished_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', zip_code_ope VARCHAR(255) NOT NULL, city_ope VARCHAR(255) NOT NULL, street_ope VARCHAR(255) NOT NULL, INDEX IDX_281453489395C3F3 (customer_id), INDEX IDX_281453481B65292 (employe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, zipcode VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, isconfirmed TINYINT(1) NOT NULL, gender VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customers ADD CONSTRAINT FK_62534E21727ACA70 FOREIGN KEY (parent_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE employes ADD CONSTRAINT FK_A94BC0F0727ACA70 FOREIGN KEY (parent_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE operations ADD CONSTRAINT FK_281453489395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE operations ADD CONSTRAINT FK_281453481B65292 FOREIGN KEY (employe_id) REFERENCES employes (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customers DROP FOREIGN KEY FK_62534E21727ACA70');
        $this->addSql('ALTER TABLE employes DROP FOREIGN KEY FK_A94BC0F0727ACA70');
        $this->addSql('ALTER TABLE operations DROP FOREIGN KEY FK_281453489395C3F3');
        $this->addSql('ALTER TABLE operations DROP FOREIGN KEY FK_281453481B65292');
        $this->addSql('DROP TABLE customers');
        $this->addSql('DROP TABLE employes');
        $this->addSql('DROP TABLE operations');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
