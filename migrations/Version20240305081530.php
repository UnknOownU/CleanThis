<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305081530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE zipcode zipcode VARCHAR(10) DEFAULT NULL, CHANGE city city VARCHAR(50) DEFAULT NULL, CHANGE street street VARCHAR(100) DEFAULT NULL, CHANGE phone phone VARCHAR(25) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` CHANGE zipcode zipcode VARCHAR(10) NOT NULL, CHANGE city city VARCHAR(50) NOT NULL, CHANGE street street VARCHAR(100) NOT NULL, CHANGE phone phone VARCHAR(25) NOT NULL');
    }
}
