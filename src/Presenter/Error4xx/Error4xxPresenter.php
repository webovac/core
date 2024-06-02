<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Error4xx;

use Nette\Application\UI\Presenter;
use Webovac\Core\Presenter\CorePresenter;
use Webovac\Core\Presenter\ErrorPresenter;


/**
 * @property Error4xxTemplate $template
 */
class Error4xxPresenter extends Presenter
{
	use ErrorPresenter;
	use CorePresenter;
//	use StylePresenter;
}