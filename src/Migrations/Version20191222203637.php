<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191222203637 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE crawled_domain_pattern_crawled_domain DROP FOREIGN KEY FK_94FB5012BECDB64A');
        $this->addSql('ALTER TABLE crawled_domain_pattern_crawled_domain DROP FOREIGN KEY FK_94FB5012A498F5A2');
        $this->addSql('CREATE TABLE crawling_pattern (id INT AUTO_INCREMENT NOT NULL, pattern VARCHAR(255) NOT NULL, urls_quantity INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crawling_pattern_crawling_history (crawling_pattern_id INT NOT NULL, crawling_history_id INT NOT NULL, INDEX IDX_579A7EB77AD5D18C (crawling_pattern_id), INDEX IDX_579A7EB793E4F7D1 (crawling_history_id), PRIMARY KEY(crawling_pattern_id, crawling_history_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crawling_history (id INT AUTO_INCREMENT NOT NULL, domain_id INT NOT NULL, crawled_links INT NOT NULL, extracted_links INT NOT NULL, created_at DATETIME NOT NULL, file_name VARCHAR(255) NOT NULL, INDEX IDX_4380125B115F0EE5 (domain_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE domain (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_A7A91E0B5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE crawling_pattern_crawling_history ADD CONSTRAINT FK_579A7EB77AD5D18C FOREIGN KEY (crawling_pattern_id) REFERENCES crawling_pattern (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crawling_pattern_crawling_history ADD CONSTRAINT FK_579A7EB793E4F7D1 FOREIGN KEY (crawling_history_id) REFERENCES crawling_history (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crawling_history ADD CONSTRAINT FK_4380125B115F0EE5 FOREIGN KEY (domain_id) REFERENCES domain (id)');
        $this->addSql('DROP TABLE crawled_domain');
        $this->addSql('DROP TABLE crawled_domain_pattern');
        $this->addSql('DROP TABLE crawled_domain_pattern_crawled_domain');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE crawling_pattern_crawling_history DROP FOREIGN KEY FK_579A7EB77AD5D18C');
        $this->addSql('ALTER TABLE crawling_pattern_crawling_history DROP FOREIGN KEY FK_579A7EB793E4F7D1');
        $this->addSql('ALTER TABLE crawling_history DROP FOREIGN KEY FK_4380125B115F0EE5');
        $this->addSql('CREATE TABLE crawled_domain (id INT AUTO_INCREMENT NOT NULL, domain_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, crawled_links INT NOT NULL, extracted_links INT NOT NULL, file_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_D539D54FF3FF5361 (domain_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE crawled_domain_pattern (id INT AUTO_INCREMENT NOT NULL, pattern VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, urls_quantity INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE crawled_domain_pattern_crawled_domain (crawled_domain_pattern_id INT NOT NULL, crawled_domain_id INT NOT NULL, INDEX IDX_94FB5012A498F5A2 (crawled_domain_pattern_id), INDEX IDX_94FB5012BECDB64A (crawled_domain_id), PRIMARY KEY(crawled_domain_pattern_id, crawled_domain_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE crawled_domain_pattern_crawled_domain ADD CONSTRAINT FK_94FB5012A498F5A2 FOREIGN KEY (crawled_domain_pattern_id) REFERENCES crawled_domain_pattern (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crawled_domain_pattern_crawled_domain ADD CONSTRAINT FK_94FB5012BECDB64A FOREIGN KEY (crawled_domain_id) REFERENCES crawled_domain (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE crawling_pattern');
        $this->addSql('DROP TABLE crawling_pattern_crawling_history');
        $this->addSql('DROP TABLE crawling_history');
        $this->addSql('DROP TABLE domain');
    }
}
