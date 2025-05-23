<?php declare(strict_types = 1);

namespace App\Core\Enum;

use App\Core\Trait\TEnumFunctions;

enum UserRole: string
{

	use TEnumFunctions;

	private const LABELS = [
		self::ADMIN->value => 'Admin',
		self::AUTHOR->value => 'Author',
		self::READER->value => 'Reader',
	];

	case ADMIN = 'admin';

	case AUTHOR = 'author';

	case READER = 'reader';

	public function label(): string
	{
		return self::LABELS[$this->value];
	}

}
