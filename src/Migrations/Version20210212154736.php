<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210212154736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add audio_waveform (AudioWaveform) table';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('
            CREATE TABLE audio_waveform (
                id INT AUTO_INCREMENT NOT NULL,
                source_id VARCHAR(255) NOT NULL,
                waveform JSON NOT NULL,
                created DATETIME NOT NULL,
                updated DATETIME NOT NULL,
                PRIMARY KEY(id)
            ) 
            
            DEFAULT CHARACTER SET utf8mb4 
            
            COLLATE `utf8mb4_unicode_ci` 
            
            ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('
            DROP TABLE audio_waveform
        ');
    }
}
