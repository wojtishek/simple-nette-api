<?php declare(strict_types = 1);

namespace App\Core\DTO;

use App\Core\Database\Entity\User;
use OpenApi\Attributes as OA;

#[OA\Schema(
	schema: 'UserDTO',
	title: 'UserDTO',
	description: 'User endpoints',
	properties: [
		new OA\Property(property: 'id', description: 'User ID', type: 'integer'),
		new OA\Property(property: 'name', description: 'User name', type: 'string'),
		new OA\Property(property: 'email', description: 'User email', type: 'string'),
		new OA\Property(property: 'role', description: 'User role', type: 'string'),
	],
	type: 'object'
)]
class UserDTO
{

	public function __construct(
		public int $id,
		public string $name,
		public string $email,
		public string $role,
	)
	{
	}

	public static function fromEntity(User $user): self
	{
		return new self(
			$user->getId(),
			$user->getName(),
			$user->getEmail(),
			$user->getRole()->value,
		);
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function setEmail(string $email): void
	{
		$this->email = $email;
	}

	public function getRole(): string
	{
		return $this->role;
	}

	public function setRole(string $role): void
	{
		$this->role = $role;
	}

}
