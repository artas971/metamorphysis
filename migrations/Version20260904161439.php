<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260904161439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE prestation ADD label_collectif VARCHAR(100) DEFAULT NULL');
        $this->addSql("UPDATE prestation SET label_collectif = 'ATELIER COLLECTIF' WHERE label_collectif IS NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE prestation DROP label_collectif');
    }
}
