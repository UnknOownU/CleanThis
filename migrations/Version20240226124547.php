<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240226124547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE documents (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, salarie_id INT DEFAULT NULL, operation_id INT DEFAULT NULL, type INT NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_A2B072889395C3F3 (customer_id), INDEX IDX_A2B072885859934A (salarie_id), INDEX IDX_A2B0728844AC3583 (operation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE operations (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, salarie_id INT DEFAULT NULL, type INT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, status INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', rdv_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', street_op VARCHAR(255) NOT NULL, zipcode_op VARCHAR(5) NOT NULL, city_op VARCHAR(50) NOT NULL, finished_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_281453489395C3F3 (customer_id), INDEX IDX_281453485859934A (salarie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, lastname VARCHAR(50) NOT NULL, firstname VARCHAR(50) NOT NULL, street VARCHAR(255) NOT NULL, zipcode VARCHAR(10) NOT NULL, city VARCHAR(100) NOT NULL, phone VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B072889395C3F3 FOREIGN KEY (customer_id) REFERENCES operations (id)');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B072885859934A FOREIGN KEY (salarie_id) REFERENCES operations (id)');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B0728844AC3583 FOREIGN KEY (operation_id) REFERENCES operations (id)');
        $this->addSql('ALTER TABLE operations ADD CONSTRAINT FK_281453489395C3F3 FOREIGN KEY (customer_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE operations ADD CONSTRAINT FK_281453485859934A FOREIGN KEY (salarie_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B072889395C3F3');
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B072885859934A');
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B0728844AC3583');
        $this->addSql('ALTER TABLE operations DROP FOREIGN KEY FK_281453489395C3F3');
        $this->addSql('ALTER TABLE operations DROP FOREIGN KEY FK_281453485859934A');
        $this->addSql('DROP TABLE documents');
        $this->addSql('DROP TABLE operations');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
