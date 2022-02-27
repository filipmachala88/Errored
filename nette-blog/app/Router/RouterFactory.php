<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		$router->addRoute('prihlaseni/', 'Sign:in');
		$router->addRoute('clanky/', 'Post:show');
		$router->addRoute('upravit/<postId>', 'Post:manipulate');
		$router->addRoute('vytvorit/', 'Post:manipulate');
		$router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}
