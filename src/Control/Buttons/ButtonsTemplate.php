<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use App\Control\BaseTemplate;
use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Latte\Attributes\TemplateFunction;
use Stepapo\Utils\Model\Collection;
use Webovac\Core\Control\MenuItem\MenuItemTemplate;
use Webovac\Core\Model\CmsEntity;


class ButtonsTemplate extends BaseTemplate
{
	public PageData $pageData;
	public WebData $webData;
	public LanguageData $languageData;
	public DataModel $dataModel;
	public ?CmsEntity $entity;
	/** @var Collection<PageData> */ public Collection $pageDatas;


	#[TemplateFunction]
	public function renderMenuItem(PageData $pageData, ?CmsEntity $linkedEntity = null): void
	{
		$t = $pageData->getCollection('translations')->getBy(['language' => $this->languageData->id]);
		$this->getLatte()->render(__DIR__ . '/../MenuItem/menuItem.latte', new MenuItemTemplate(
			webData: $this->webData,
			pageData: $pageData,
			pageTranslationData: $t ?: $pageData->getCollection('translations')->getBy(['language' => $this->webData->defaultLanguage]),
			languageData: $this->languageData,
			targetLanguageData: $t ? $this->languageData : $this->dataModel->languageRepository->getById($this->webData->defaultLanguage),
			entity: $this->entity,
			linkedEntity: $linkedEntity,
			dataModel: $this->dataModel,
			context: 'buttons',
			presenter: $this->presenter,
		));
	}
}
