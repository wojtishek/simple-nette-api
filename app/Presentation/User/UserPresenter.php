<?php declare(strict_types = 1);

namespace App\Presentation\User;

use App\Core\Database\Entity\User;
use App\Core\Database\Mapper\UserMapper;
use App\Core\DTO\UserDTO;
use App\Core\Enum\UserRole;
use App\Presentation\BaseApiPresenter;
use Doctrine\ORM\EntityRepository;
use Nette\Application\Attributes\Requires;
use OpenApi\Attributes as OA;
use function assert;

#[OA\Response(
	response: 'user',
	description: 'User response',
	content: new OA\JsonContent(
		properties: [
			new OA\Property(property: 'code', description: 'HTTP status code', type: 'integer', example: 200),
			new OA\Property(
				property: 'message',
				description: 'HTTP status message',
				type: 'string',
				example: 'Success',
			),
			new OA\Property(
				property: 'data',
				description: 'Response data',
				properties: [
					new OA\Property(
						property: 'user',
						ref: '#/components/schemas/UserDTO',
						description: 'Users list',
						type: 'object',
					),
				],
			),
		],
	),
)]

#[OA\Response(
	response: 'users',
	description: 'Users list response',
	content: new OA\JsonContent(
		properties: [
			new OA\Property(property: 'code', description: 'HTTP status code', type: 'integer', example: 200),
			new OA\Property(
				property: 'message',
				description: 'HTTP status message',
				type: 'string',
				example: 'Success',
			),
			new OA\Property(
				property: 'data',
				description: 'Response data',
				properties: [
					new OA\Property(
						property: 'users',
						description: 'Users list',
						type: 'array',
						items: new OA\Items(
							ref: '#/components/schemas/UserDTO',
							type: 'object',
						),
					),
				],
			),
		],
	),
)]

#[OA\RequestBody(
	request: 'user',
	description: 'User request',
	required: true,
	content: new OA\JsonContent(
		properties: [
			new OA\Property(property: 'email', description: 'User email', type: 'string'),
			new OA\Property(property: 'password', description: 'User password', type: 'string'),
			new OA\Property(property: 'name', description: 'User name', type: 'string'),
			new OA\Property(property: 'role', description: 'User role', type: 'string', enum: UserRole::class),
		],
	),
)]

#[OA\Parameter(
	parameter: 'userId',
	name: 'id',
	description: 'User id',
	in: 'path',
	required: true,
	schema: new OA\Schema(type: 'integer'),
)]
#[OA\Parameter(
	parameter: 'userName',
	name: 'name',
	description: 'User name',
	in: 'query',
	required: true,
	schema: new OA\Schema(type: 'string'),
)]
#[OA\Parameter(
	parameter: 'userEmail',
	name: 'email',
	description: 'User email',
	in: 'query',
	required: true,
	schema: new OA\Schema(type: 'string'),
)]
#[OA\Parameter(
	parameter: 'userPassword',
	name: 'password',
	description: 'User password',
	in: 'query',
	required: true,
	schema: new OA\Schema(type: 'string'),
)]
#[OA\Parameter(
	parameter: 'userRole',
	name: 'role',
	description: 'User role',
	in: 'query',
	required: true,
	schema: new OA\Schema(type: 'string'),
)]
class UserPresenter extends BaseApiPresenter
{

	private array $requiredParameters = ['name', 'email', 'password', 'role'];

	private EntityRepository $userRepository;

	public int $id;

	protected function startup(): void
	{
		parent::startup();
		$this->userRepository = $this->entityManager->getRepository('App\Core\Database\Entity\User');
	}

	#[Requires(methods: ['POST', 'GET', 'PUT', 'DELETE'])]
	public function actionDefault(int|null $id = null): void
	{
		$method = $this->getRequestMethod();
		$this->allowAction($method, 'user');
		$this->callEndpoint($method, $id);
	}

	private function callEndpoint(string $method, int|null $id): void
	{
		match ($method) {
			'POST' => $this->callCreateUser(),
			'GET' => $this->callReadUser($id),
			'PUT' => $this->callUpdateUser($id),
			'DELETE' => $this->callDeleteUser($id),
			default => $this->sendErrorResponse($this->responseFormatService->error405()),
		};
	}

	#[OA\Post(
		path: '/users',
		description: 'Create new user',
		requestBody: new OA\RequestBody(
			ref: '#/components/requestBodies/user',
		),
		tags: ['Users'],
		parameters: [
			new OA\HeaderParameter(ref: '#/components/parameters/authorization'),
			new OA\Parameter(ref: '#/components/parameters/userName'),
			new OA\Parameter(ref: '#/components/parameters/userEmail'),
			new OA\Parameter(ref: '#/components/parameters/userPassword'),
			new OA\Parameter(ref: '#/components/parameters/userRole'),
		],
		responses: [
			new OA\Response(ref: '#/components/responses/users', response: '200', description: 'OK'),
			new OA\Response(ref: '#/components/responses/error400', response: 400),
		],
	)]
	private function callCreateUser(): void
	{
		$body = $this->checkRequiredParameters($this->requiredParameters);
		$emailValidationError = $this->userService->validateEmail($body['email']);
		if ($emailValidationError) {
			$this->sendErrorResponse($this->responseFormatService->error400($emailValidationError));
		}

		$user = $this->userService->saveUser($body);
		$this->sendSuccessResponse(['user' => UserDTO::fromEntity($user)]);
	}

	#[OA\Get(
		path: '/users',
		description: 'Get user list',
		tags: ['Users'],
		parameters: [
			new OA\HeaderParameter(ref: '#/components/parameters/authorization'),
		],
		responses: [
			new OA\Response(ref: '#/components/responses/users', response: '200', description: 'OK'),
			new OA\Response(ref: '#/components/responses/error404', response: 404),
		],
	)]
	#[OA\Get(
		path: '/users/{id}',
		description: 'Get user by id',
		tags: ['Users'],
		parameters: [
			new OA\HeaderParameter(ref: '#/components/parameters/authorization'),
			new OA\Parameter(ref: '#/components/parameters/userId'),
		],
		responses: [
			new OA\Response(ref: '#/components/responses/user', response: '200', description: 'OK'),
			new OA\Response(ref: '#/components/responses/error404', response: 404),
		],
	)]
	private function callReadUser(int|null $id): void
	{
		if ($id) {
			$user = $this->userRepository->findOneBy(['id' => $id]);
			if (!$user) {
				$this->sendErrorResponse($this->responseFormatService->error404('User not found'));
			}

			assert($user instanceof User);
			$this->sendSuccessResponse(['user' => UserMapper::mapToDTO($user)]);
		} else {
			$users = $this->userRepository->findAll();
			$this->sendSuccessResponse(['users' => UserMapper::collectionDTO($users)]);
		}
	}

	#[OA\Put(
		path: '/users/{id}',
		description: 'Update user',
		requestBody: new OA\RequestBody(
			ref: '#/components/requestBodies/user',
		),
		tags: ['Users'],
		parameters: [
			new OA\HeaderParameter(ref: '#/components/parameters/authorization'),
			new OA\Parameter(ref: '#/components/parameters/userId'),
			new OA\Parameter(ref: '#/components/parameters/userName'),
			new OA\Parameter(ref: '#/components/parameters/userEmail'),
			new OA\Parameter(ref: '#/components/parameters/userPassword'),
			new OA\Parameter(ref: '#/components/parameters/userRole'),
		],
		responses: [
			new OA\Response(ref: '#/components/responses/user', response: '200', description: 'OK'),
			new OA\Response(ref: '#/components/responses/error400', response: 400),
			new OA\Response(ref: '#/components/responses/error404', response: 404),

		],
	)]
	private function callUpdateUser(int|null $id): void
	{
		if (!$id) {
			$this->sendErrorResponse($this->responseFormatService->error400('User id is required'));
		}

		$body = $this->checkRequiredParameters($this->requiredParameters, true);
		$user = $this->userRepository->findOneBy(['id' => $id]);
		if (!$user) {
			$this->sendErrorResponse($this->responseFormatService->error404('User not found'));
		}

		assert($user instanceof User);
		$checkEmail = $this->userService->validateEmail($body['email']);
		if ($checkEmail) {
			$this->sendErrorResponse($this->responseFormatService->error400($checkEmail));
		}

		$user->setName($body['name'] ?? $user->getName());
		$user->setEmail($body['email'] ?? $user->getEmail());
		$user->setPasswordHash(
			isset($body['password']) ? $this->passwords->hash($body['password']) : $user->getPasswordHash(),
		);
		$user->setRole(isset($body['role']) ? UserRole::from($body['role']) : $user->getRole());
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		$this->sendSuccessResponse(['user' => UserMapper::mapToDTO($user)]);
	}

	#[OA\Delete(
		path: '/users/{id}',
		description: 'Delete user',
		tags: ['Users'],
		parameters: [
			new OA\HeaderParameter(ref: '#/components/parameters/authorization'),
			new OA\Parameter(ref: '#/components/parameters/userId'),
		],
		responses: [
			new OA\Response(ref: '#/components/responses/user', response: '200', description: 'OK'),
			new OA\Response(ref: '#/components/responses/error400', response: 400),
			new OA\Response(ref: '#/components/responses/error404', response: 404),
		],
	)]
	private function callDeleteUser(int|null $id): void
	{
		if (!$id) {
			$this->sendErrorResponse($this->responseFormatService->error400('User id is required'));
		}

		$user = $this->userRepository->findOneBy(['id' => $id]);
		if (!$user) {
			$this->sendErrorResponse($this->responseFormatService->error404('User not found'));
		}

		assert($user instanceof User);
		$userDTO = UserMapper::mapToDTO($user);
		$this->entityManager->remove($user);
		$this->entityManager->flush();
		$this->sendSuccessResponse(['user' => $userDTO]);
	}

}
