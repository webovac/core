<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter;

use Nette\Application\Attributes\Persistent;
use Nette\DI\Attributes\Inject;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\ModuleChecker;


trait ErrorPresenter
{
	#[Persistent] public string $host;
	#[Persistent] public string $basePath;
	#[Persistent] public string $lang;
	#[Inject] public ModuleChecker $moduleChecker;
	#[Inject] public CmsUser $cmsUser;


	public function injectErrorStartup(): void
	{
		$this->onStartup[] = function () {
			$request = $this->getHttpRequest();
			$this->host = $request->getUrl()->getHost();
			$this->basePath = '~';
			$this->lang = 'cs';
		};
	}
}
