<?php declare(strict_types = 1);

namespace App\Core\Database\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;

#[OA\Schema(
	schema: 'Article',
	title: 'Article',
)]
#[ORM\Entity]
#[ORM\Table(name: 'article')]
class Article
{

	#[OA\Property(
		description: 'Article ID',
		example: 1,
	)]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: Types::INTEGER)]
	private int $id;

	#[OA\Property(
		description: 'Article title',
		example: 'Article title',
	)]
	#[ORM\Column(length: 255)]
	private string $title;

	#[OA\Property(
		description: 'Article content',
		example: 'Article content',
	)]
	#[ORM\Column(type: Types::TEXT)]
	private string $content;

	#[OA\Property(
		description: 'Article author',
		example: 1,
	)]
	#[ORM\ManyToOne(targetEntity: User::class)]
	private User $author;

	#[ORM\Column(type: Types::DATETIME_IMMUTABLE, updatable: false)]
	private DateTimeImmutable $createdAt;

	#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
	private DateTimeImmutable $updatedAt;

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

	public function setCreatedAt(): void
	{
		$this->createdAt = new DateTimeImmutable('now');
	}

	public function getUpdatedAt(): DateTimeImmutable
	{
		return $this->updatedAt;
	}

	public function setUpdatedAt(): void
	{
		$this->updatedAt = new DateTimeImmutable('now');
	}

}
