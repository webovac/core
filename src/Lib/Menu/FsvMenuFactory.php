<?php

namespace Webovac\Core\Lib\Menu;

use Stepapo\Menu\UI\Menu;


class FsvMenuFactory
{
	public function __construct() {}


	public function create(string $file, array $params = []): Menu
	{
		$menu = Menu::createFromNeon($file, $params);
		$menu->setTemplateFile(__DIR__ . '/templates/menu.latte');
		return $menu;
	}
}
