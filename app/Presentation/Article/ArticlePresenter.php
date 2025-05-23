<?php declare(strict_types = 1);

namespace App\Presentation\Article;

use App\Core\Database\Entity\Article;
use App\Core\Database\Mapper\ArticleMapper;
use App\Presentation\BaseApiPresenter;
use Nette\Application\Attributes\Requires;
use OpenApi\Attributes as OA;
use function assert;

#[OA\Response(
	response: 'article',
	description: 'Article response',
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
						property: 'article',
						ref: '#/components/schemas/ArticleDTO',
						description: 'Article list',
						type: 'object',
					),
				],
			),
		],
	),
)]

#[OA\Response(
	response: 'articles',
	description: 'Article response',
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
						property: 'article',
						ref: '#/components/schemas/ArticleDTO',
						description: 'Article list',
						type: 'array',
						items: new OA\Items(ref: '#/components/schemas/ArticleDTO', type: 'object'),
					),
				],
			),
		],
	),
)]

#[OA\RequestBody(
	request: 'article',
	description: 'Article request',
	required: true,
	content: new OA\JsonContent(
		properties: [
			new OA\Property(property: 'title', description: 'User email', type: 'string'),
			new OA\Property(property: 'content', description: 'User password', type: 'string'),
		],
	),
)]

#[OA\Parameter(
	parameter: 'articleId',
	name: 'id',
	description: 'Article id',
	in: 'path',
	required: true,
	schema: new OA\Schema(type: 'integer'),
)]
#[OA\Parameter(
	parameter: 'articleTitle',
	name: 'name',
	description: 'Article title',
	in: 'query',
	required: true,
	schema: new OA\Schema(type: 'string'),
)]
#[OA\Parameter(
	parameter: 'articleContent',
	name: 'email',
	description: 'Article content',
	in: 'query',
	required: true,
	schema: new OA\Schema(type: 'string'),
)]
class ArticlePresenter extends BaseApiPresenter
{

	private array $requiredParameters = ['title', 'content'];

	#[Requires(methods: ['POST', 'GET', 'PUT', 'DELETE'])]
	public function actionDefault(int|null $id = null): void
	{
		$method = $this->getRequestMethod();
		$this->allowAction($method, 'article', $id);
		$this->callEndpoint($method, $id);
	}

	private function callEndpoint(string $method, int|null $id): void
	{
		match ($method) {
			'POST' => $this->callCreateArticle(),
			'GET' => $this->callReadArticle($id),
			'PUT' => $this->callUpdateArticle($id),
			'DELETE' => $this->callDeleteArticle($id),
			default => $this->sendErrorResponse($this->responseFormatService->error405()),
		};
	}

	#[OA\Post(
		path: '/articles',
		description: 'Create new article',
		requestBody: new OA\RequestBody(ref: '#/components/requestBodies/article'),
		tags: ['Articles'],
		parameters: [
			new OA\HeaderParameter(ref: '#/components/parameters/authorization'),
			new OA\Parameter(ref: '#/components/parameters/articleTitle'),
			new OA\Parameter(ref: '#/components/parameters/articleContent'),
		],
		responses: [
			new OA\Response(ref: '#/components/responses/article', response: '200', description: 'OK'),
		],
	)]
	private function callCreateArticle(): void
	{
		$body = $this->checkRequiredParameters($this->requiredParameters);

		$article = new Article();
		$article->setTitle($body['title']);
		$article->setContent($body['content']);
		$article->setAuthor($this->user);
		$article->setCreatedAt();
		$article->setUpdatedAt();
		$this->entityManager->persist($article);
		$this->entityManager->flush();
		$this->sendSuccessResponse(['request' => ArticleMapper::mapToDTO($article)]);
	}

	#[OA\Get(
		path: '/articles',
		description: 'Get article list',
		tags: ['Articles'],
		parameters: [
			new OA\HeaderParameter(ref: '#/components/parameters/authorization'),
		],
		responses: [
			new OA\Response(ref: '#/components/responses/articles', response: '200', description: 'OK'),
		],
	)]
	#[OA\Get(
		path: '/articles/{id}',
		description: 'Get article by id',
		tags: ['Articles'],
		parameters: [
			new OA\HeaderParameter(ref: '#/components/parameters/authorization'),
			new OA\Parameter(ref: '#/components/parameters/articleId'),
		],
		responses: [
			new OA\Response(ref: '#/components/responses/article', response: '200', description: 'OK'),
		],
	)]
	private function callReadArticle(int|null $id): void
	{
		if ($id) {
			$article = $this->entityManager->getRepository('App\Core\Database\Entity\Article')->findOneBy(
				['id' => $id],
			);
			if (!$article) {
				$this->sendErrorResponse($this->responseFormatService->error404('Article not found'));
			}

			assert($article instanceof Article);
			$this->sendSuccessResponse(['article' => ArticleMapper::mapToDTO($article)]);
		} else {
			$articles = $this->entityManager->getRepository('App\Core\Database\Entity\Article')->findAll();
			$this->sendSuccessResponse(['articles' => ArticleMapper::collectionDTO($articles)]);
		}
	}

	#[OA\Put(
		path: '/articles/{id}',
		description: 'Update article',
		requestBody: new OA\RequestBody(
			ref: '#/components/requestBodies/article',
		),
		tags: ['Articles'],
		parameters: [
			new OA\HeaderParameter(ref: '#/components/parameters/authorization'),
			new OA\Parameter(ref: '#/components/parameters/articleId'),
			new OA\Parameter(ref: '#/components/parameters/articleTitle'),
			new OA\Parameter(ref: '#/components/parameters/articleContent'),
		],
		responses: [
			new OA\Response(ref: '#/components/responses/article', response: '200', description: 'OK'),
		],
	)]
	private function callUpdateArticle(int|null $id): void
	{
		$this->allowAction('PUT', 'article', $id, 'author');
		if (!$id) {
			$this->sendErrorResponse($this->responseFormatService->error400('Article id is required'));
		}

		$body = $this->checkRequiredParameters($this->requiredParameters, true);
		$article = $this->entityManager->getRepository('App\Core\Database\Entity\Article')->findOneBy(['id' => $id]);
		if (!$article) {
			$this->sendErrorResponse($this->responseFormatService->error404('Article not found'));
		}

		assert($article instanceof Article);
		$article->setTitle($body['title']);
		$article->setContent($body['content']);
		$article->setUpdatedAt();
		$this->entityManager->persist($article);
		$this->entityManager->flush();
		$this->sendSuccessResponse(['article' => ArticleMapper::mapToDTO($article)]);
	}

	#[OA\Delete(
		path: '/articles/{id}',
		description: 'Delete article',
		tags: ['Articles'],
		parameters: [
			new OA\HeaderParameter(ref: '#/components/parameters/authorization'),
			new OA\Parameter(ref: '#/components/parameters/articleId'),
		],
		responses: [
			new OA\Response(ref: '#/components/responses/article', response: '200', description: 'OK'),
		],
	)]
	private function callDeleteArticle(int|null $id): void
	{
		$this->allowAction('DELETE', 'article', $id, 'author');
		if (!$id) {
			$this->sendErrorResponse($this->responseFormatService->error400('Article id is required'));
		}

		$article = $this->entityManager->getRepository('App\Core\Database\Entity\Article')->findOneBy(['id' => $id]);
		if (!$article) {
			$this->sendErrorResponse($this->responseFormatService->error404('Article not found'));
		}

		assert($article instanceof Article);
		$articleDTO = ArticleMapper::mapToDTO($article);
		$this->entityManager->remove($article);
		$this->entityManager->flush();
		$this->sendSuccessResponse(['article' => $articleDTO]);
	}

}
