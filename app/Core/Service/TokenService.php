<?php declare(strict_types = 1);

namespace App\Core\Service;

use App\Core\Database\Entity\AccessToken;
use App\Core\Database\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Nette\Application\BadRequestException;
use Nette\Utils\Random;
use Throwable;
use function array_merge;
use function time;

class TokenService
{

	private string $algorithm = 'HS256';

	public function __construct(
		private readonly string $secretKey,
		private readonly int $tokenExpiration,
		private readonly EntityManager $entityManager,
	)
	{
	}

	public function saveToken(User $user, string $accessToken): void
	{
		$token = new AccessToken();
		$token->setAccessToken($accessToken);
		$token->setUser($user);
		$token->setIssuedAt(new DateTimeImmutable());
		$token->setExpiresAt(new DateTimeImmutable('@' . (time() + $this->tokenExpiration)));
		$this->entityManager->persist($token);
		$this->entityManager->flush();
	}

	public function extendTokenExpiration(string $token): void
	{
		$token = $this->entityManager->getRepository('App\Core\Database\Entity\AccessToken')->findOneBy(
			['accessToken' => $token],
		);
		if (!$token) {
			throw new BadRequestException('Invalid token');
		}

		$token->setExpiresAt(new DateTimeImmutable('@' . (time() + $this->tokenExpiration)));
		$this->entityManager->persist($token);
		$this->entityManager->flush();
	}

	public function checkToken(string $token): bool
	{
		$token = $this->entityManager->getRepository('App\Core\Database\Entity\AccessToken')->findOneBy(
			['accessToken' => $token],
		);
		if (!$token) {
			throw new BadRequestException('Invalid token');
		}

		if ($token->getExpiresAt() < new DateTimeImmutable()) {
			throw new BadRequestException('Token expired');
		}

		$this->extendTokenExpiration($token->getAccessToken());

		return true;
	}

	public function encodeToken(array $payload): string
	{
		$payload = array_merge([
			'iat' => $payload['iat'] ?? time(),
			'exp' => $payload['exp'] ?? time() + $this->tokenExpiration,
			'uid' => Random::generate(16),
		], $payload);

		return JWT::encode($payload, $this->secretKey, $this->algorithm);
	}

	public function decodeToken(string $token): array
	{
		try {
			$decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));

			return (array) $decoded;
		} catch (Throwable $e) {
			throw new BadRequestException('Invalid token: ' . $e->getMessage());
		}
	}

	public function getTokenExpiration(int $multiplier = 1): int
	{
		return $this->tokenExpiration * $multiplier;
	}

}
