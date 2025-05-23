<?php declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250518212928 extends AbstractMigration
{

	public function getDescription(): string
	{
		return 'Create access token table';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql(<<<'SQL'
			CREATE TABLE access_token (id INT AUTO_INCREMENT NOT NULL, access_token TEXT NOT NULL, issued_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_B6A2DD68A76ED395 (user_id), PRIMARY KEY(id))
		SQL);
		$this->addSql(<<<'SQL'
			ALTER TABLE access_token ADD CONSTRAINT FK_B6A2DD68A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
		SQL);
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql(<<<'SQL'
			ALTER TABLE access_token DROP FOREIGN KEY FK_B6A2DD68A76ED395
		SQL);
		$this->addSql(<<<'SQL'
			DROP TABLE access_token
		SQL);
	}

}
