<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210922162051 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add compression to KodiImageType';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE kodi_image_type ADD compression INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE kodi_image_type DROP compression');
    }
}
