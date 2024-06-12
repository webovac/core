<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Home;

use App\Presenter\BasePresenter;


/**
 * @property HomeTemplate $template
 */
class HomePresenter extends BasePresenter
{
	public function renderDefault(string $pageName, mixed $id = null, mixed $parentId = null): void
	{
	}
}
