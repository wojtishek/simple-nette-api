<?php declare(strict_types = 1);

namespace App\Presentation\Error\Error4xx;

use Nette;
use Nette\Application\Attributes\Requires;

/**
 * Handles 4xx HTTP error responses.
 */
#[Requires(methods: '*', forward: true)]
final class Error4xxPresenter extends Nette\Application\UI\Presenter
{

	public function actionDefault(Nette\Application\BadRequestException $exception): void
	{
		$code = $exception->getCode();
		$message = $exception->getMessage();
		$this->sendJson(
			[
				'code' => $code,
				'message' => $message,
			],
		);
	}

}
