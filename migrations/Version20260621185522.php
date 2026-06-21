<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260621185522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE horaire_hebdomadaire (id INT AUTO_INCREMENT NOT NULL, jour INT NOT NULL, est_ouvert TINYINT NOT NULL, ouverture_matin TIME DEFAULT NULL, fermeture_matin TIME DEFAULT NULL, ouverture_apres_midi TIME DEFAULT NULL, fermeture_apres_midi TIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE indisponibilite (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, debut DATETIME NOT NULL, fin DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE page DROP contenu');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849559E45C554 FOREIGN KEY (prestation_id) REFERENCES prestation (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE section ADD CONSTRAINT FK_2D737AEFC4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
        $this->addSql('ALTER TABLE user CHANGE telephone telephone VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE horaire_hebdomadaire');
        $this->addSql('DROP TABLE indisponibilite');
        $this->addSql('ALTER TABLE page ADD contenu LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849559E45C554');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE section DROP FOREIGN KEY FK_2D737AEFC4663E4');
        $this->addSql('ALTER TABLE user CHANGE telephone telephone VARCHAR(20) NOT NULL');
    }
}
