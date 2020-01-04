<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200104113537 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE domain (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_A7A91E0B5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crawling_history (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', domain_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', crawled_links INT NOT NULL, extracted_links INT NOT NULL, created_at DATETIME NOT NULL, file_name VARCHAR(255) NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_4380125B115F0EE5 (domain_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crawling_pattern (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', pattern VARCHAR(255) NOT NULL, urls_quantity INT NOT NULL, UNIQUE INDEX UNIQ_C7869E9EA3BCFC8E (pattern), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crawling_pattern_crawling_history (crawling_pattern_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', crawling_history_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_579A7EB77AD5D18C (crawling_pattern_id), INDEX IDX_579A7EB793E4F7D1 (crawling_history_id), PRIMARY KEY(crawling_pattern_id, crawling_history_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE crawling_history ADD CONSTRAINT FK_4380125B115F0EE5 FOREIGN KEY (domain_id) REFERENCES domain (id)');
        $this->addSql('ALTER TABLE crawling_pattern_crawling_history ADD CONSTRAINT FK_579A7EB77AD5D18C FOREIGN KEY (crawling_pattern_id) REFERENCES crawling_pattern (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crawling_pattern_crawling_history ADD CONSTRAINT FK_579A7EB793E4F7D1 FOREIGN KEY (crawling_history_id) REFERENCES crawling_history (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE crawling_history DROP FOREIGN KEY FK_4380125B115F0EE5');
        $this->addSql('ALTER TABLE crawling_pattern_crawling_history DROP FOREIGN KEY FK_579A7EB793E4F7D1');
        $this->addSql('ALTER TABLE crawling_pattern_crawling_history DROP FOREIGN KEY FK_579A7EB77AD5D18C');
        $this->addSql('DROP TABLE domain');
        $this->addSql('DROP TABLE crawling_history');
        $this->addSql('DROP TABLE crawling_pattern');
        $this->addSql('DROP TABLE crawling_pattern_crawling_history');
    }
}
