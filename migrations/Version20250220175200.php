<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220175200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article CHANGE titre titre VARCHAR(255) NOT NULL, CHANGE contenu contenu LONGTEXT NOT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE categorie categorie VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE reclamation CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE reservation ADD film_id INT DEFAULT NULL, ADD nombre_places INT NOT NULL, ADD status VARCHAR(20) NOT NULL, ADD titre VARCHAR(100) NOT NULL, ADD selected_seats LONGTEXT DEFAULT NULL, CHANGE salle_id salle_id INT DEFAULT NULL, CHANGE association_id association_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955567F5183 FOREIGN KEY (film_id) REFERENCES film (id)');
        $this->addSql('CREATE INDEX IDX_42C84955567F5183 ON reservation (film_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article CHANGE titre titre VARCHAR(30) NOT NULL, CHANGE contenu contenu VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) NOT NULL, CHANGE categorie categorie VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE commentaire CHANGE date date VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reclamation CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955567F5183');
        $this->addSql('DROP INDEX IDX_42C84955567F5183 ON reservation');
        $this->addSql('ALTER TABLE reservation DROP film_id, DROP nombre_places, DROP status, DROP titre, DROP selected_seats, CHANGE salle_id salle_id INT NOT NULL, CHANGE association_id association_id INT NOT NULL');
    }
}
