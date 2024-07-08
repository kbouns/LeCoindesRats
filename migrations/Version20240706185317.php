<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240706185317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deal DROP FOREIGN KEY FK_E3FEC11679F37AE5');
        $this->addSql('DROP INDEX IDX_E3FEC11679F37AE5 ON deal');
        $this->addSql('ALTER TABLE deal CHANGE id_user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE deal ADD CONSTRAINT FK_E3FEC116A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_E3FEC116A76ED395 ON deal (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deal DROP FOREIGN KEY FK_E3FEC116A76ED395');
        $this->addSql('DROP INDEX IDX_E3FEC116A76ED395 ON deal');
        $this->addSql('ALTER TABLE deal CHANGE user_id id_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE deal ADD CONSTRAINT FK_E3FEC11679F37AE5 FOREIGN KEY (id_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_E3FEC11679F37AE5 ON deal (id_user_id)');
    }
}
