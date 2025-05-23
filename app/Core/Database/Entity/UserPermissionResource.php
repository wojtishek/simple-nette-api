<?php declare(strict_types = 1);

namespace App\Core\Database\Entity;

use App\Core\Enum\UserRole;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_permission_resource')]
class UserPermissionResource
{

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: Types::INTEGER)]
	private int $id;

	#[ORM\Column(length: 255, enumType: UserRole::class)]
	private UserRole $role;

	#[ORM\Column(length: 255)]
	private string $permission;

	#[ORM\Column(length: 255)]
	private string $resource;

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
	}

	public function getRole(): UserRole
	{
		return $this->role;
	}

	public function setRole(UserRole $role): void
	{
		$this->role = $role;
	}

	public function getPermission(): string
	{
		return $this->permission;
	}

	public function setPermission(string $permission): void
	{
		$this->permission = $permission;
	}

	public function getResource(): string
	{
		return $this->resource;
	}

	public function setResource(string $resource): void
	{
		$this->resource = $resource;
	}

}
