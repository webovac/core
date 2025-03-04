<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nette\Application\UI\Control;
use Stepapo\Utils\Service;
use Webovac\Core\Control\MenuItem\MenuItemTemplate;
use Webovac\Core\Model\CmsEntity;


class MenuItemRenderer implements Service
{
	public function __construct(
		private DataModel $dataModel,
	) {}


	public function render(
		string $context,
		Control $control,
		WebData $webData,
		PageData $pageData,
		LanguageData $languageData,
		bool $checkActive,
		?CmsEntity $entity,
		?CmsEntity $linkedEntity = null,
	): void
	{
		$t = $pageData->getCollection('translations')->getByKey($languageData->id);
		$control->template->getLatte()->render(__DIR__ . '/../templates/menuItem.latte', new MenuItemTemplate(
			latte: $control->template->getLatte(),
			webData: $webData,
			pageData: $pageData,
			pageTranslationData: $t ?: $pageData->getCollection('translations')->getByKey($webData->defaultLanguage),
			languageData: $languageData,
			targetLanguageData: $t ? $languageData : $this->dataModel->getLanguageData($webData->defaultLanguage),
			checkActive: $checkActive,
			entity: $entity,
			linkedEntity: $linkedEntity,
			dataModel: $this->dataModel,
			context: $context,
			presenter: $control->presenter,
		));
	}
}