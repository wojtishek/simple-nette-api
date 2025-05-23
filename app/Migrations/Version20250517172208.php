<?php declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250517172208 extends AbstractMigration
{

	public function getDescription(): string
	{
		return 'Initial structure';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql(<<<'SQL'
			CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, author_id INT DEFAULT NULL, INDEX IDX_23A0E66F675F31B (author_id), PRIMARY KEY(id))
		SQL);
		$this->addSql(<<<'SQL'
			CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id))
		SQL);
		$this->addSql(<<<'SQL'
			ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE CASCADE
		SQL);
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql(<<<'SQL'
			ALTER TABLE article DROP FOREIGN KEY FK_23A0E66F675F31B
		SQL);
		$this->addSql(<<<'SQL'
			DROP TABLE article
		SQL);
		$this->addSql(<<<'SQL'
			DROP TABLE user
		SQL);
	}

}
