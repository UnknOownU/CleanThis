<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240222114249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE documents (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, salarie_id INT DEFAULT NULL, type INT NOT NULL, url VARCHAR(255) NOT NULL, idoperation VARCHAR(50) NOT NULL, INDEX IDX_A2B0728819EB6921 (client_id), INDEX IDX_A2B072885859934A (salarie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE operation (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, salarie_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, price INT NOT NULL, description VARCHAR(255) NOT NULL, status INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', rdv_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', city_opp VARCHAR(100) NOT NULL, zipcode VARCHAR(5) NOT NULL, street VARCHAR(255) NOT NULL, date_fin_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', type INT NOT NULL, INDEX IDX_1981A66D19EB6921 (client_id), INDEX IDX_1981A66D5859934A (salarie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, lastname VARCHAR(50) NOT NULL, firstname VARCHAR(50) NOT NULL, street VARCHAR(255) NOT NULL, zipcode VARCHAR(10) NOT NULL, city VARCHAR(100) NOT NULL, phone VARCHAR(20) NOT NULL, id_compt_op INT NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B0728819EB6921 FOREIGN KEY (client_id) REFERENCES operation (id)');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B072885859934A FOREIGN KEY (salarie_id) REFERENCES operation (id)');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66D19EB6921 FOREIGN KEY (client_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66D5859934A FOREIGN KEY (salarie_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B0728819EB6921');
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B072885859934A');
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66D19EB6921');
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66D5859934A');
        $this->addSql('DROP TABLE documents');
        $this->addSql('DROP TABLE operation');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
