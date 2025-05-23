<?php declare(strict_types = 1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{

	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList();
		$router
			->addRoute('users/<action>[/<id>]', 'User:default')
			->addRoute('articles/<action>[/<id>]', 'Article:default')
			->addRoute('auth/<action>', 'Auth:default');
		$router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
		return $router;
	}

}
