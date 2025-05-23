<?php declare(strict_types = 1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520214123 extends AbstractMigration
{

	public function getDescription(): string
	{
		return 'Fill user_permission_resource table';
	}

	public function up(Schema $schema): void
	{
		$this->addSql(<<<'SQL'
			INSERT INTO user_permission_resource (role, permission, resource) VALUES
				('admin', 'create', 'user'),
				('admin', 'read', 'user'),
				('admin', 'update', 'user'),
				('admin', 'delete', 'user'),
				('admin', 'create', 'article'),
				('admin', 'read', 'article'),
				('admin', 'update', 'article'),
				('admin', 'delete', 'article'),
				('author', 'create', 'article'),
				('author', 'read', 'article'),
				('author', 'update', 'article'),
				('author', 'delete', 'article'),
				('reader', 'read', 'article')
		SQL);
	}

	public function down(Schema $schema): void
	{
		$this->addSql(<<<'SQL'
			SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE `api`.`user_permission_resource`; SET FOREIGN_KEY_CHECKS = 1
		SQL);
	}

}
