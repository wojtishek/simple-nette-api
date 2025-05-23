<?php declare(strict_types = 1);

namespace App\Core\Trait;

use function array_column;

trait TEnumFunctions
{

	public static function getValues(): array
	{
		return array_column(self::cases(), 'value');
	}

	public static function toSelect(): array
	{
		$labels = [];
		foreach (self::cases() as $case) {
			$labels[$case->value] = $case->label();
		}

		return $labels;
	}

	public static function getLabel(string $value): string
	{
		return self::toSelect()[$value];
	}

}
