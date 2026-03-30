<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213151442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation ADD salle_id INT NOT NULL, ADD association_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955EFB9C8A5 FOREIGN KEY (association_id) REFERENCES association (id)');
        $this->addSql('CREATE INDEX IDX_42C84955DC304035 ON reservation (salle_id)');
        $this->addSql('CREATE INDEX IDX_42C84955EFB9C8A5 ON reservation (association_id)');
        $this->addSql('ALTER TABLE ressource ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE ressource ADD CONSTRAINT FK_939F4544A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_939F4544A76ED395 ON ressource (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ressource DROP FOREIGN KEY FK_939F4544A76ED395');
        $this->addSql('DROP INDEX IDX_939F4544A76ED395 ON ressource');
        $this->addSql('ALTER TABLE ressource DROP user_id');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955DC304035');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955EFB9C8A5');
        $this->addSql('DROP INDEX IDX_42C84955DC304035 ON reservation');
        $this->addSql('DROP INDEX IDX_42C84955EFB9C8A5 ON reservation');
        $this->addSql('ALTER TABLE reservation DROP salle_id, DROP association_id');
    }
}
