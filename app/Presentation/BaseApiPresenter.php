<?php declare(strict_types = 1);

namespace App\Presentation;

use App\Core\Database\Entity\User;
use App\Core\Exception\PermissionNotAllowed;
use App\Core\Service\ResponseFormatService;
use App\Core\Service\TokenService;
use App\Core\Service\UserService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Security\Passwords;
use Nette\Utils\Json;
use OpenApi\Attributes as OA;
use function array_diff;
use function array_keys;
use function explode;
use function implode;
use function str_starts_with;

#[OA\Info(
	version: '1.0.0',
	title: 'Simple nette api',
)]
#[OA\OpenApi(
	security: [['bearerAuth' => []]],
	tags: [
		new OA\Tag(name: 'Auth'),
		new OA\Tag(name: 'Users'),
		new OA\Tag(name: 'Articles'),
	]
)]
#[OA\SecurityScheme(
	securityScheme: 'bearerAuth',
	type: 'http',
	name: 'Authorization',
	in: 'header',
	bearerFormat: 'JWT',
	scheme: 'bearer',
)]
#[OA\HeaderParameter(
	parameter: 'authorization',
	name: 'Authorization',
	required: true,
	schema: new OA\Schema(type: 'string', default: 'Bearer {token}')
)]
#[OA\HeaderParameter(
	parameter: 'authorizationOptional',
	name: 'Authorization',
	required: false,
	schema: new OA\Schema(type: 'string', default: 'Bearer {token}')
)]
#[OA\Response(
	response: 'error400',
	description: 'Bad request',
	content: new OA\JsonContent(
	properties: [
		new OA\Property(property: 'code', description: 'HTTP status code', type: 'integer', example: 400),
		new OA\Property(property: 'message', description: 'HTTP status message', type: 'string'),
		new OA\Property(property: 'error', description: 'Detailed error', type: 'string')
	])
)]
#[OA\Response(
	response: 'error403',
	description: 'Forbidden',
	content: new OA\JsonContent(
	properties: [
		new OA\Property(property: 'code', description: 'HTTP status code', type: 'integer', example: 403),
		new OA\Property(property: 'message', description: 'HTTP status message', type: 'string'),
		new OA\Property(property: 'error', description: 'Detailed error', type: 'string')
	])
)]
#[OA\Response(
	response: 'error404',
	description: 'Not found',
	content: new OA\JsonContent(
	properties: [
		new OA\Property(property: 'code', description: 'HTTP status code', type: 'integer', example: 404),
		new OA\Property(property: 'message', description: 'HTTP status message', type: 'string'),
		new OA\Property(property: 'error', description: 'Detailed error', type: 'string')
	])
)]

class BaseApiPresenter extends Presenter
{

	protected const METHOD_TO_PERMISSION = [
		'GET' => 'read',
		'POST' => 'create',
		'PUT' => 'update',
		'DELETE' => 'delete',
	];

	protected User $user;

	public function __construct(
		protected readonly ResponseFormatService $responseFormatService,
		protected readonly TokenService $tokenService,
		protected readonly EntityManager $entityManager,
		protected readonly Passwords $passwords,
		protected readonly UserService $userService,
	)
	{
		parent::__construct();
	}

	public function actionDefault(): void
	{
		$this->sendErrorResponse($this->responseFormatService->error400());
	}

	protected function checkRequiredParameters(array $required, bool $anyOf = false): array
	{
		$body = $this->getRequestBody();
		if (!$body) {
			$this->sendErrorResponse($this->responseFormatService->error400(error: 'Missing body'));
		}

		if ($anyOf) {
			return $body;
		}

		$diff = array_diff($required, array_keys($body));
		if (!empty($diff)) {
			$this->sendErrorResponse(
				$this->responseFormatService->error400(error: 'Missing parameter: ' . implode(', ', $diff)),
			);
		}

		return $body;
	}

	protected function getRequestBody()
	{
		return Json::decode($this->getHttpRequest()->getRawBody(), true);
	}

	protected function getRequestMethod()
	{
		return $this->getHttpRequest()->getMethod();
	}

	protected function sendErrorResponse(array $response): void
	{
		$this->getHttpResponse()->setCode($response['code']);
		$this->sendJson($response);
	}

	protected function sendSuccessResponse(array $response): void
	{
		$this->sendJson($this->responseFormatService->success($response));
	}

	protected function allowAction(
		string $method,
		string $resourceType,
		int|null $resourceId = null,
		string|null $columnName = null,
	): void
	{

		try {
			$user = $this->userService->authorizeRequest(
				$this->getHeaderToken(),
				self::METHOD_TO_PERMISSION[$method],
				$resourceType,
				$resourceId,
				$columnName,
			);
			$this->user = $user;
		} catch (BadRequestException $e) {
			$this->sendErrorResponse($this->responseFormatService->error400($e->getMessage()));
		} catch (OptimisticLockException | ORMException $e) {
			$this->sendErrorResponse($this->responseFormatService->error400($e->getMessage()));
		} catch (PermissionNotAllowed $e) {
			$this->sendErrorResponse($this->responseFormatService->error403($e->getMessage()));
		}
	}

	protected function getHeaderToken(): string
	{
		$headerToken = $this->getHttpRequest()->getHeader('Authorization');
		if (!$headerToken) {
			throw new BadRequestException('Missing Authorization header');
		}

		if (!str_starts_with($headerToken, 'Bearer ')) {
			throw new BadRequestException('Invalid Authorization header');
		}

		$token = explode(' ', $headerToken)[1];
		if (!$token) {
			throw new BadRequestException('Missing token');
		}

		return $token;
	}

}
