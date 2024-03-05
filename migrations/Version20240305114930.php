<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305114930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE documents (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, salarie_id INT DEFAULT NULL, operation_id INT DEFAULT NULL, type INT NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_A2B072889395C3F3 (customer_id), INDEX IDX_A2B072885859934A (salarie_id), INDEX IDX_A2B0728844AC3583 (operation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B072889395C3F3 FOREIGN KEY (customer_id) REFERENCES operation (id)');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B072885859934A FOREIGN KEY (salarie_id) REFERENCES operation (id)');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B0728844AC3583 FOREIGN KEY (operation_id) REFERENCES operation (id)');
        $this->addSql('ALTER TABLE user ADD id_google VARCHAR(255) DEFAULT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL, CHANGE zipcode zipcode VARCHAR(10) DEFAULT NULL, CHANGE city city VARCHAR(50) DEFAULT NULL, CHANGE street street VARCHAR(100) DEFAULT NULL, CHANGE phone phone VARCHAR(25) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B072889395C3F3');
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B072885859934A');
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B0728844AC3583');
        $this->addSql('DROP TABLE documents');
        $this->addSql('ALTER TABLE `user` DROP id_google, CHANGE password password VARCHAR(255) NOT NULL, CHANGE zipcode zipcode VARCHAR(10) NOT NULL, CHANGE city city VARCHAR(50) NOT NULL, CHANGE street street VARCHAR(100) NOT NULL, CHANGE phone phone VARCHAR(25) NOT NULL');
    }
}
