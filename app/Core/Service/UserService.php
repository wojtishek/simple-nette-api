<?php declare(strict_types = 1);

namespace App\Core\Service;

use App\Core\Database\Entity\User;
use App\Core\Database\Entity\UserPermissionResource;
use App\Core\Enum\UserRole;
use App\Core\Exception\PermissionNotAllowed;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Nette\Security\Passwords;
use Nette\Utils\Strings;
use Nette\Utils\Validators;
use ReflectionClass;
use function assert;

class UserService
{

	protected array $permissions = [];

	public function __construct(
		private readonly EntityManager $entityManager,
		private readonly TokenService $tokenService,
		private readonly Passwords $passwords,
	)
	{
		$this->permissions = $this->entityManager->getRepository(UserPermissionResource::class)->findAll();
	}

	public function saveUser(array $data): User
	{
		$user = new User();
		$user->setEmail($data['email']);
		$user->setName($data['name']);
		$user->setPasswordHash($this->passwords->hash($data['password']));
		$user->setRole(UserRole::from($data['role']));
		$this->entityManager->persist($user);
		$this->entityManager->flush();

		return $user;
	}

	public function validateEmail(string $email): bool|string
	{
		if (!Validators::isEmail($email)) {
			return 'Email is not valid';
		}

		$user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
		if ($user !== null) {
			return 'Email is already in use';
		}

		return false;
	}

	/**
	 * @throws OptimisticLockException
	 * @throws PermissionNotAllowed
	 * @throws ORMException
	 */
	public function authorizeRequest(
		string $token,
		string $permission,
		string $resourceType,
		int|null $resourceId = null,
		string|null $columnName = null,
	): User|bool
	{
		$this->tokenService->checkToken($token);
		$decoded = $this->tokenService->decodeToken($token);
		$user = $this->entityManager->find(User::class, $decoded['userId']);
		assert($user instanceof User);
		$this->hasPermission($user, $permission, $resourceType, $resourceId, $columnName);
		$this->tokenService->extendTokenExpiration($token);

		return $user;
	}

	private function hasPermission(
		User $user,
		string $requiredPermission,
		string $resourceType,
		int|null $resourceId,
		string|null $columnName,
	): void
	{
		if ($user->getRole()->value === 'admin') {
			return;
		}

		foreach ($this->permissions as $permissionResource) {
			if ($permissionResource->getRole()->value !== $user->getRole()->value) {
				continue;
			}

			if ($permissionResource->getPermission() !== $requiredPermission) {
				continue;
			}

			if ($permissionResource->getResource() !== $resourceType) {
				continue;
			}

			if ($resourceId) {
				$resourceType = Strings::firstUpper($resourceType);
				$reflection = new ReflectionClass('App\Core\Database\Entity\\' . $resourceType);
				$resourceTypeObject = $this->entityManager->getRepository($reflection->getName())->findOneBy(
					['id' => $resourceId],
				);
				if (!$resourceTypeObject) {
					continue;
				}

				assert($resourceTypeObject instanceof $resourceType);
				if ($columnName) {
					$columnName = Strings::firstUpper($columnName);
					$resourceValue = $reflection->getMethod('get' . $columnName)->invoke($resourceTypeObject);
					if ($resourceValue->getId() !== $user->getId()) {
						continue;
					}
				}
			}

			return;
		}

		throw new PermissionNotAllowed('You are not allowed to access this resource');
	}

}
