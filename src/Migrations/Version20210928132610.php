<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210928132610 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add version to KodiImage';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE kodi_image ADD version INT DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE kodi_image DROP version');
    }
}
