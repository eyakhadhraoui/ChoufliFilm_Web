<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213144630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE film (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(30) NOT NULL, directeur VARCHAR(30) NOT NULL, note DOUBLE PRECISION NOT NULL, genre VARCHAR(30) NOT NULL, description VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, duree INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salle (id INT AUTO_INCREMENT NOT NULL, nom_salle VARCHAR(30) NOT NULL, nbr_places INT NOT NULL, type_salle VARCHAR(50) NOT NULL, etat_salle VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salle_film (salle_id INT NOT NULL, film_id INT NOT NULL, INDEX IDX_3FC4F7A2DC304035 (salle_id), INDEX IDX_3FC4F7A2567F5183 (film_id), PRIMARY KEY(salle_id, film_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE salle_film ADD CONSTRAINT FK_3FC4F7A2DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE salle_film ADD CONSTRAINT FK_3FC4F7A2567F5183 FOREIGN KEY (film_id) REFERENCES film (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salle_film DROP FOREIGN KEY FK_3FC4F7A2DC304035');
        $this->addSql('ALTER TABLE salle_film DROP FOREIGN KEY FK_3FC4F7A2567F5183');
        $this->addSql('DROP TABLE film');
        $this->addSql('DROP TABLE salle');
        $this->addSql('DROP TABLE salle_film');
    }
}
