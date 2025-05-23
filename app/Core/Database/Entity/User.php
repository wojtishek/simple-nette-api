<?php declare(strict_types = 1);

namespace App\Core\Database\Entity;

use App\Core\Enum\UserRole;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'User', title: 'User')]
#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User
{

	#[OA\Property(description: 'User ID', example: 1)]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: Types::INTEGER)]
	private int $id;

	#[OA\Property(description: 'User email', example: 'email@example.com')]
	#[ORM\Column(length: 255, unique: true)]
	private string $email;

	#[OA\Property(title: 'Hashed user password', description: 'User password')]
	#[ORM\Column(length: 255)]
	private string $passwordHash;

	#[OA\Property(description: 'User full name', example: 'John Doe')]
	#[ORM\Column(length: 255)]
	private string $name;

	#[OA\Property(description: 'User role', example: 'author')]
	#[ORM\Column(length: 255, enumType: UserRole::class)]
	private UserRole $role;

	public function getRole(): UserRole
	{
		return $this->role;
	}

	public function setRole(UserRole $role): void
	{
		$this->role = $role;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function setEmail(string $email): void
	{
		$this->email = $email;
	}

	public function getPasswordHash(): string
	{
		return $this->passwordHash;
	}

	public function setPasswordHash(string $passwordHash): void
	{
		$this->passwordHash = $passwordHash;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
	}

}
