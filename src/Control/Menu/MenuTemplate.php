<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use App\Control\BaseTemplate;
use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Layout\LayoutData;
use App\Model\Page\PageData;
use App\Model\Theme\ThemeData;
use App\Model\Web\WebData;
use Latte\Attributes\TemplateFunction;
use Nette\Application\LinkGenerator;
use Stepapo\Utils\Model\Collection;
use Webovac\Core\Control\MenuItem\MenuItemTemplate;
use Webovac\Core\Model\CmsEntity;


class MenuTemplate extends BaseTemplate
{
	public WebData $webData;
	public string $logoUrl;
	public PageData $pageData;
	/** @var Collection<PageData> */ public Collection $pageDatas;
	public LanguageData $languageData;
	public LayoutData $layoutData;
	public ?PageData $homePageData;
	public DataModel $dataModel;
	public LinkGenerator $linkGenerator;
	public ?CmsEntity $entity;
	public string $title;
	public string $wwwDir;
	public bool $isError;
	public bool $hasSearch;
	/** @var string[] */ public array $availableTranslations;
	/** @var ThemeData[] */ public Collection $themeDatas;


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
			context: 'primary',
			presenter: $this->presenter,
		));
	}
}
