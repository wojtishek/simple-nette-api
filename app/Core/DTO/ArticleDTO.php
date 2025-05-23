<?php declare(strict_types = 1);

namespace App\Core\DTO;

use App\Core\Database\Entity\Article;
use App\Core\Database\Entity\User;
use DateTimeImmutable;
use OpenApi\Attributes as OA;

#[OA\Schema(
	schema: 'ArticleDTO',
	title: 'ArticleDTO',
	description: 'Article endpoints',
	properties: [
		new OA\Property(property: 'id', description: 'Article id', type: 'integer'),
		new OA\Property(property: 'title', description: 'Article title', type: 'string'),
		new OA\Property(property: 'content', description: 'Article content', type: 'string'),
		new OA\Property(property: 'author', description: 'Article author ID', type: 'integer'),
		new OA\Property(property: 'createdAt', description: 'Article creation date', type: 'string', format: 'date-time'),
		new OA\Property(property: 'updatedAt', description: 'Article update date', type: 'string', format: 'date-time')
	]
)]
class ArticleDTO
{

	public function __construct(
		public int $id,
		public string $title,
		public string $content,
		public User $author,
		public DateTimeImmutable $createdAt,
		public DateTimeImmutable $updatedAt,
	)
	{
	}

	public static function fromEntity(Article $article): self
	{
		return new self(
			$article->getId(),
			$article->getTitle(),
			$article->getContent(),
			$article->getAuthor(),
			$article->getCreatedAt(),
			$article->getUpdatedAt(),
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

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): void
	{
		$this->title = $title;
	}

	public function getContent(): string
	{
		return $this->content;
	}

	public function setContent(string $content): void
	{
		$this->content = $content;
	}

	public function getAuthor(): User
	{
		return $this->author;
	}

	public function setAuthor(User $author): void
	{
		$this->author = $author;
	}

	public function getCreatedAt(): DateTimeImmutable
	{
		return $this->createdAt;
	}

	public function setCreatedAt(DateTimeImmutable $createdAt): void
	{
		$this->createdAt = $createdAt;
	}

	public function getUpdatedAt(): DateTimeImmutable
	{
		return $this->updatedAt;
	}

	public function setUpdatedAt(DateTimeImmutable $updatedAt): void
	{
		$this->updatedAt = $updatedAt;
	}

}
