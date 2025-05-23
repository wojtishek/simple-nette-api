<?php declare(strict_types = 1);

namespace App\Presentation\Auth;

use App\Core\Database\Entity\User;
use App\Core\DTO\UserDTO;
use App\Core\Enum\UserRole;
use App\Core\Exception\PermissionNotAllowed;
use App\Presentation\BaseApiPresenter;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Nette\Application\Attributes\Requires;
use Nette\Application\BadRequestException;
use OpenApi\Attributes as OA;
use function assert;

#[OA\Parameter(
	parameter: 'authEmail',
	name: 'email',
	description: 'User email',
	in: 'query',
	required: true,
	schema: new OA\Schema(type: 'string'),
)]
#[OA\Parameter(
	parameter: 'authPassword',
	name: 'password',
	description: 'User password',
	in: 'query',
	required: true,
	schema: new OA\Schema(type: 'string'),
)]
#[OA\Parameter(
	parameter: 'authName',
	name: 'name',
	description: 'User name',
	in: 'query',
	required: true,
	schema: new OA\Schema(type: 'string'),
)]
#[OA\Parameter(
	parameter: 'authRole',
	name: 'role',
	description: 'User role',
	in: 'query',
	required: true,
	schema: new OA\Schema(type: 'string'),
)]
class AuthPresenter extends BaseApiPresenter
{

	#[OA\Post(
		path: '/auth/register',
		description: 'Register new user. In case no user is registered, the role of first user will be set to admin.',
		requestBody: new OA\RequestBody(
			required: true,
			content: new OA\JsonContent(
				properties: [
					new OA\Property(property: 'email', description: 'User email', type: 'string'),
					new OA\Property(property: 'password', description: 'User password', type: 'string'),
					new OA\Property(property: 'name', description: 'User name', type: 'string'),
					new OA\Property(property: 'role', description: 'User role', type: 'string', enum: UserRole::class),
				],
			),
		),
		tags: ['Auth'],
		parameters: [
			new OA\HeaderParameter(ref: '#/components/parameters/authorizationOptional'),
			new OA\Parameter(ref: '#/components/parameters/authEmail'),
			new OA\Parameter(ref: '#/components/parameters/authPassword'),
			new OA\Parameter(ref: '#/components/parameters/authName'),
			new OA\Parameter(ref: '#/components/parameters/authRole'),
		],
		responses: [
			new OA\Response(response: '200', description: 'OK', content: new OA\JsonContent(
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
								property: 'request',
								ref: '#/components/schemas/UserDTO',
								description: 'User data',
								type: 'object',
							),
						],
					),
				],
			)),
			new OA\Response(ref: '#/components/responses/error400', response: 400),
			new OA\Response(ref: '#/components/responses/error403', response: 403),
		],
	)]
	#[Requires(methods: 'POST')]
	public function actionRegister(): void
	{
		$requiredParameters = ['email', 'password', 'name'];
		$body = $this->checkRequiredParameters($requiredParameters);
		$userCount = $this->entityManager->getRepository('App\Core\Database\Entity\User')->count();
		try {
			if ($userCount > 0) {
				$this->userService->authorizeRequest($this->getHeaderToken(), 'create', 'user');
				$requiredParameters[] = 'role';
				$body = $this->checkRequiredParameters($requiredParameters);
			} else {
				$body['role'] = UserRole::ADMIN;
			}

			$checkEmail = $this->userService->validateEmail($body['email']);
			if ($checkEmail) {
				$this->sendErrorResponse($this->responseFormatService->error400($checkEmail));
			}

			$user = $this->userService->saveUser($body);
			$this->sendSuccessResponse(['request' => UserDTO::fromEntity($user)]);
		} catch (OptimisticLockException | ORMException | BadRequestException $e) {
			$this->sendErrorResponse($this->responseFormatService->error400($e->getMessage()));
		} catch (PermissionNotAllowed $e) {
			$this->sendErrorResponse($this->responseFormatService->error403($e->getMessage()));
		}
	}

	#[OA\Post(
		path: '/auth/login',
		description: 'Login user.',
		requestBody: new OA\RequestBody(
			required: true,
			content: new OA\JsonContent(
				properties: [
					new OA\Property(property: 'email', description: 'User email', type: 'string'),
					new OA\Property(property: 'password', description: 'User password', type: 'string'),
				],
				type: 'object',
			),
		),
		tags: ['Auth'],
		parameters: [
			new OA\Parameter(ref: '#/components/parameters/authEmail'),
			new OA\Parameter(ref: '#/components/parameters/authPassword'),
		],
		responses: [
			new OA\Response(response: '200', description: 'OK', content: new OA\JsonContent(
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
								property: 'token',
								description: 'JWT token',
								type: 'string',
								example: '<KEY>',
							),
						],
						type: 'object',
					),
				],
				type: 'object',
			)),
			new OA\Response(ref: '#/components/responses/error400', response: 400),
		],
	)]
	#[Requires(methods: 'POST')]
	public function actionLogin(): void
	{
		$requiredParameters = ['email', 'password'];
		$body = $this->checkRequiredParameters($requiredParameters);
		$user = $this->entityManager->getRepository('App\Core\Database\Entity\User')->findOneBy(
			['email' => $body['email']],
		);
		if (!$user || !$this->passwords->verify($body['password'], $user->getPasswordHash())) {
			$this->sendErrorResponse($this->responseFormatService->error400('Invalid credentials'));
		}

		assert($user instanceof User);
		$accessToken = $this->tokenService->encodeToken(
			[
				'userId' => $user->getId(),
			],
		);
		$this->tokenService->saveToken($user, $accessToken);
		$this->sendSuccessResponse(['accessToken' => $accessToken]);
	}

}
