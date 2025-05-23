<?php declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520213903 extends AbstractMigration
{

	public function getDescription(): string
	{
		return 'Create user_permission_resource table';
	}

	public function up(Schema $schema): void
	{
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql(<<<'SQL'
			CREATE TABLE user_permission_resource (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(255) NOT NULL, permission VARCHAR(255) NOT NULL, resource VARCHAR(255) NOT NULL, PRIMARY KEY(id))
		SQL);
	}

	public function down(Schema $schema): void
	{
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql(<<<'SQL'
			DROP TABLE user_permission_resource
		SQL);
	}

}
