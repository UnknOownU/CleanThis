<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240319084424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE operation ADD attachment VARCHAR(255) NOT NULL, ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE customer_id customer_id INT NOT NULL, CHANGE type type VARCHAR(100) NOT NULL, CHANGE price price INT NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE rdv_at rdv_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE zipcode_ope zipcode_ope VARCHAR(10) NOT NULL, CHANGE city_ope city_ope VARCHAR(255) NOT NULL, CHANGE street_ope street_ope VARCHAR(255) NOT NULL, CHANGE finished_at finished_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user ADD id_google VARCHAR(255) DEFAULT NULL, ADD reset_token VARCHAR(255) DEFAULT NULL, DROP cpt_ope, DROP is_verified, CHANGE password password VARCHAR(255) DEFAULT NULL, CHANGE zipcode zipcode VARCHAR(10) DEFAULT NULL, CHANGE city city VARCHAR(50) DEFAULT NULL, CHANGE street street VARCHAR(100) DEFAULT NULL, CHANGE phone phone VARCHAR(25) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE operation DROP attachment, DROP updated_at, CHANGE customer_id customer_id INT DEFAULT NULL, CHANGE type type VARCHAR(50) NOT NULL, CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE status status INT NOT NULL, CHANGE zipcode_ope zipcode_ope VARCHAR(20) NOT NULL, CHANGE city_ope city_ope VARCHAR(50) NOT NULL, CHANGE street_ope street_ope VARCHAR(50) NOT NULL, CHANGE finished_at finished_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE rdv_at rdv_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE `user` ADD cpt_ope INT NOT NULL, ADD is_verified TINYINT(1) NOT NULL, DROP id_google, DROP reset_token, CHANGE password password VARCHAR(255) NOT NULL, CHANGE zipcode zipcode VARCHAR(10) NOT NULL, CHANGE city city VARCHAR(50) NOT NULL, CHANGE street street VARCHAR(100) NOT NULL, CHANGE phone phone VARCHAR(25) NOT NULL');
    }
}
