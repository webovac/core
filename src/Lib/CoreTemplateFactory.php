<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Nette\Application\UI\Template;
use Nette\DI\Attributes\Inject;
use Stepapo\Utils\Latte\Filters;


trait CoreTemplateFactory
{
	#[Inject] public Dir $dir;
	#[Inject] public ModuleChecker $moduleChecker;
	#[Inject] public CmsTranslator $translator;
	#[Inject] public CmsUser $cmsUser;
	#[Inject] public ColorMixer $colorMixer;
	#[Inject] public KeyProvider $keyProvider;


	public function injectCoreCreate(): void
	{
		$this->onCreate[] = function (Template $template) {
			$template->setTranslator($this->translator);
			$template->addFilter('plural', [Filters::class, 'plural']);
			$template->addFilter('intlDate', [Filters::class, 'intlDate']);
			$template->addFilter('monthName', [Filters::class, 'monthName']);
			$template->addFilter('duration', [Filters::class, 'duration']);
			$template->addFilter('mTime', fn($path) => filemtime($this->dir->getWwwDir() . DIRECTORY_SEPARATOR . $path));
			$template->addFilter('rgb', $this->colorMixer->rgb(...));
			$template->addFilter('tint', $this->colorMixer->tint(...));
			$template->addFilter('shade', $this->colorMixer->shade(...));
			$template->addFilter('replaceKey', $this->keyProvider->replaceKey(...));
			$template->addFunction('isModuleInstalled', fn(string $name) => $this->moduleChecker->isModuleInstalled($name));
			$template->addFunction('core', fn(string $name) => __DIR__ . "/../templates/$name.latte");
		};
	}
}
