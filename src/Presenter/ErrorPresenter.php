<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter;

use Latte\Loaders\StringLoader;
use Latte\Sandbox\SecurityPolicy;
use Nette\Application\Attributes\Persistent;
use Nette\DI\Attributes\Inject;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Relationships\IRelationshipCollection;
use Stepapo\Dataset\Latte\Filters;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\ModuleChecker;
use Webovac\Core\Model\CmsData;


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
			$this->basePath = '';
			$this->lang = 'cs';
		};
	}


	public function injectErrorRender(): void
	{
		$this->onRender[] = function () {
			$this->template->cmsUser = $this->cmsUser;
			$this->template->addFilter('plural', [Filters::class, 'plural']);
			$this->template->addFilter('intlDate', [Filters::class, 'intlDate']);
			$this->template->addFilter('mTime', fn($path) => filemtime($this->dir->getWwwDir() . DIRECTORY_SEPARATOR . $path));
			$this->template->addFunction('isModuleInstalled', fn(string $name) => $this->moduleChecker->isModuleInstalled($name));
			$file = __DIR__ . "/../Presenter/Error4xx/{$this->getParameter('exception')->getCode()}.latte";
			$this->template->getLatte()->setLoader(new StringLoader([
				'@layout.file' => file_get_contents($this->dir->getAppDir() . "/Presenter/@layout.latte"),
				'main.file' => file_get_contents(is_file($file) ? $file : __DIR__ . '/4xx.latte'),
			]))
				->setSandboxMode()
				->setPolicy(
					SecurityPolicy::createSafePolicy()
						->allowTags(['include', 'control', 'plink', 'contentType'])
						->allowFilters(['noescape', 'mTime'])
						->allowProperties(\stdClass::class, SecurityPolicy::All)
						->allowProperties(IEntity::class, SecurityPolicy::All)
						->allowProperties(CmsData::class, SecurityPolicy::All)
						->allowMethods(IEntity::class, SecurityPolicy::All)
						->allowMethods(IRelationshipCollection::class, SecurityPolicy::All)
						->allowFunctions(['is_numeric', 'max', 'isModuleInstalled', 'lcfirst'])
				);
		};
	}
}
