<?php declare(strict_types = 1);

namespace App\Core\Database\Mapper;

use App\Core\Database\Entity\Article;
use App\Core\DTO\ArticleDTO;
use function array_map;

class ArticleMapper
{

	public static function mapToDTO(Article $article): ArticleDTO
	{
		return ArticleDTO::fromEntity($article);
	}

	public static function collectionDTO(array $articles): array
	{
		return array_map(static fn (Article $article) => self::mapToDTO($article), $articles);
	}

}
