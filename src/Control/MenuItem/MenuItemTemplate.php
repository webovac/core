<?php

declare(strict_types=1);

namespace Webovac\Core\Control\MenuItem;

use Build\Model\DataModel;
use Build\Model\Language\LanguageData;
use Build\Model\Layout\LayoutData;
use Build\Model\Page\PageData;
use Build\Model\PageTranslation\PageTranslationData;
use Build\Model\Web\WebData;
use Nette\Application\UI\Presenter;
use Webovac\Core\Lib\PageActivator;
use Webovac\Core\Model\CmsEntity;


class MenuItemTemplate
{
	public ?string $href;
	public string $class;
	public ?string $icon;


	public function __construct(
		public WebData $webData,
		public PageData $pageData,
		public LayoutData $layoutData,
		public ?PageTranslationData $pageTranslationData,
		public LanguageData $languageData,
		public LanguageData $targetLanguageData,
		public bool $checkActive,
		public ?CmsEntity $entity,
		public ?CmsEntity $linkedEntity,
		public DataModel $dataModel,
		public string $context,
		public Presenter $presenter,
		public PageActivator $pageActivator,
	) {
		$this->href = $pageData->getHref($targetLanguageData, $webData, $dataModel, $presenter, $entity, $linkedEntity);
		$this->class = $pageData->getClass($context, $checkActive, $presenter, $this->pageActivator, $entity, $linkedEntity);
		$this->icon = ($linkedEntity && $pageData->isDetailRoot && method_exists($linkedEntity, 'getIcon')
			? $linkedEntity->getIcon() : null) ?: $pageData->icon;
	}
}