<?php declare(strict_types = 1);

namespace App\Core\Database\Mapper;

use App\Core\Database\Entity\User;
use App\Core\DTO\UserDTO;
use function array_map;

class UserMapper
{

	public static function mapToDTO(User $user): UserDTO
	{
		return UserDTO::fromEntity($user);
	}

	public static function collectionDTO(array $users): array
	{
		return array_map(static fn (User $user) => self::mapToDTO($user), $users);
	}

}
