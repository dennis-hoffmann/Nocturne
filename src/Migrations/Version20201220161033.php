<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201220161033 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Initial migration';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE kodi_image (id INT AUTO_INCREMENT NOT NULL, image_type_id INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, target_id INT NOT NULL, source_url LONGTEXT NOT NULL, filename VARCHAR(255) NOT NULL, INDEX IDX_FD1F3793505CDB4F (image_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kodi_image_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, width INT NOT NULL, height INT NOT NULL, path VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE playlist (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_D782112D7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE playlist_entry (id INT AUTO_INCREMENT NOT NULL, playlist_id INT NOT NULL, song_id INT NOT NULL, position INT NOT NULL, added DATETIME NOT NULL, INDEX IDX_883D84336BBD148 (playlist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kodi_image ADD CONSTRAINT FK_FD1F3793505CDB4F FOREIGN KEY (image_type_id) REFERENCES kodi_image_type (id)');
        $this->addSql('ALTER TABLE playlist ADD CONSTRAINT FK_D782112D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE playlist_entry ADD CONSTRAINT FK_883D84336BBD148 FOREIGN KEY (playlist_id) REFERENCES playlist (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE kodi_image DROP FOREIGN KEY FK_FD1F3793505CDB4F');
        $this->addSql('ALTER TABLE playlist_entry DROP FOREIGN KEY FK_883D84336BBD148');
        $this->addSql('ALTER TABLE playlist DROP FOREIGN KEY FK_D782112D7E3C61F9');
        $this->addSql('DROP TABLE kodi_image');
        $this->addSql('DROP TABLE kodi_image_type');
        $this->addSql('DROP TABLE playlist');
        $this->addSql('DROP TABLE playlist_entry');
        $this->addSql('DROP TABLE user');
    }
}
