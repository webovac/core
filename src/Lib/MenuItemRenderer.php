<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Build\Model\DataModel;
use Build\Model\Language\LanguageData;
use Build\Model\Layout\LayoutData;
use Build\Model\Page\PageData;
use Build\Model\PageTranslation\PageTranslationData;
use Build\Model\Web\WebData;
use Nette\Application\UI\Control;
use Stepapo\Utils\Service;
use Webovac\Core\Control\MenuItem\MenuItemTemplate;
use Webovac\Core\Model\CmsEntity;
use Webovac\Core\Model\Linkable;


class MenuItemRenderer implements Service
{
	public function __construct(
		private DataModel $dataModel,
		private PageActivator $pageActivator,
	) {}


	public function render(
		string $context,
		Control $control,
		WebData $webData,
		PageData $pageData,
		LayoutData $layoutData,
		LanguageData $languageData,
		bool $checkActive,
		?CmsEntity $entity,
		?CmsEntity $linkedEntity = null,
	): void
	{
		assert(is_int($webData->defaultLanguage));
		$t = $pageData->getCollection('translations')->getByKey($languageData->id);
		/** @var PageTranslationData $pageTranslationData */
		$pageTranslationData = $t ?: $pageData->getCollection('translations')->getByKey($webData->defaultLanguage);
		$control->template->getLatte()->render(__DIR__ . '/../templates/menuItem.latte', new MenuItemTemplate(
			webData: $webData,
			pageData: $pageData,
			layoutData: $layoutData,
			pageTranslationData: $pageTranslationData,
			languageData: $languageData,
			targetLanguageData: $t ? $languageData : $this->dataModel->getLanguageData($webData->defaultLanguage),
			checkActive: $checkActive,
			entity: $entity,
			linkedEntity: $linkedEntity,
			dataModel: $this->dataModel,
			context: $context,
			presenter: $control->getPresenter(),
			pageActivator: $this->pageActivator,
		));
	}
}
