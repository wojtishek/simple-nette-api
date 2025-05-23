<?php declare(strict_types = 1);

namespace App\Core\Database\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'access_token')]
class AccessToken
{

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: Types::INTEGER)]
	private int $id;

	#[ORM\Column(type: Types::TEXT)]
	private string $accessToken;

	#[ORM\Column(type: Types::DATETIME_IMMUTABLE, updatable: false)]
	private DateTimeImmutable $issuedAt;

	#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
	private DateTimeImmutable $expiresAt;

	#[ORM\ManyToOne(targetEntity: User::class)]
	private User $user;

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
	}

	public function getAccessToken(): string
	{
		return $this->accessToken;
	}

	public function setAccessToken(string $accessToken): void
	{
		$this->accessToken = $accessToken;
	}

	public function getIssuedAt(): DateTimeImmutable
	{
		return $this->issuedAt;
	}

	public function setIssuedAt(DateTimeImmutable $issuedAt): void
	{
		$this->issuedAt = $issuedAt;
	}

	public function getExpiresAt(): DateTimeImmutable
	{
		return $this->expiresAt;
	}

	public function setExpiresAt(DateTimeImmutable $expiresAt): void
	{
		$this->expiresAt = $expiresAt;
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function setUser(User $user): void
	{
		$this->user = $user;
	}

}
