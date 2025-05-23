<?php declare(strict_types = 1);

namespace App\Core\Service;

class ResponseFormatService
{

	/**
	 * Success status
	 */
	public function success(array $data): array
	{
		return [
			'code' => 200,
			'message' => 'Success',
			'data' => $data,
		];
	}

	/**
	 * Error status: Bad request
	 */
	public function error400(string $error = ''): array
	{
		return $this->formatError(400, 'Bad request', $error);
	}

	/**
	 * Error status: Unauthorized
	 */
	public function error401(string $error = ''): array
	{
		return $this->formatError(401, 'Unauthorized', $error);
	}

	/**
	 * Error status: Forbidden
	 */
	public function error403(string $error = ''): array
	{
		return $this->formatError(403, 'Forbidden', $error);
	}

	/**
	 * Error status: Not found
	 */
	public function error404(string $error = ''): array
	{
		return $this->formatError(404, 'Not found', $error);
	}

	/**
	 * Error status: Method not allowed
	 */
	public function error405(): array
	{
		return $this->formatError(405, 'Method not allowed');
	}

	/**
	 * Error status: Internal server error
	 */
	public function error500(): array
	{
		return $this->formatError(500, 'Internal server error');
	}

	private function formatError(int $code, string $message, string|null $error = null): array
	{
		$array = [
			'code' => $code,
			'message' => $message,
		];

		if ($error) {
			$array['error'] = $error;
		}

		return $array;
	}

}
