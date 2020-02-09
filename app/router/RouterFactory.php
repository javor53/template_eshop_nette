<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
                $router = new RouteList();
                
                $eshop = new RouteList('Eshop');
		$eshop[] = new Route('admin.shop/<presenter>/<action>[/<id>]', 'Homepage:default');
                $router[] = $eshop;
                
                
		$admin = new RouteList('Admin');
		$admin[] = new Route('admin/<presenter>/<action>[/<id>]', 'Homepage:default');
                $router[] = $admin;

                $front = new RouteList('Public');
		$front[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
                $router[] = $front;
                
                $router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}
