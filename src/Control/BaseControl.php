<?php

declare(strict_types=1);

namespace Webovac\Core\Control;

use Nette\Application\UI\Control;


class BaseControl extends Control
{
	public function render(): void
	{
		$this->template->render(__DIR__ . '/base.latte');
	}
}
