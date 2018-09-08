<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180908080013 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE audio_upload (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, upload_date DATETIME NOT NULL, status INT NOT NULL, is_deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE audio_upload_chunk (id INT AUTO_INCREMENT NOT NULL, audio_upload_id INT NOT NULL, filename VARCHAR(255) NOT NULL, chunk_number INT NOT NULL, upload_date DATETIME NOT NULL, INDEX IDX_1599234A989EB731 (audio_upload_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE audio_upload_chunk ADD CONSTRAINT FK_1599234A989EB731 FOREIGN KEY (audio_upload_id) REFERENCES audio_upload (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE audio_upload_chunk DROP FOREIGN KEY FK_1599234A989EB731');
        $this->addSql('DROP TABLE audio_upload');
        $this->addSql('DROP TABLE audio_upload_chunk');
    }
}
