<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240227084157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE operation (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, salarie_id INT DEFAULT NULL, type VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, price INT NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', rdv_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', zipcode_ope VARCHAR(10) NOT NULL, city_ope VARCHAR(255) NOT NULL, street_ope VARCHAR(255) NOT NULL, INDEX IDX_1981A66D9395C3F3 (customer_id), INDEX IDX_1981A66D5859934A (salarie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66D9395C3F3 FOREIGN KEY (customer_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE operation ADD CONSTRAINT FK_1981A66D5859934A FOREIGN KEY (salarie_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user ADD finished_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66D9395C3F3');
        $this->addSql('ALTER TABLE operation DROP FOREIGN KEY FK_1981A66D5859934A');
        $this->addSql('DROP TABLE operation');
        $this->addSql('ALTER TABLE `user` DROP finished_at');
    }
}
