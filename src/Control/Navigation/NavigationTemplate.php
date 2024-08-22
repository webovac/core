<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Navigation;

use App\Control\BaseTemplate;
use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Layout\LayoutData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Latte\Attributes\TemplateFunction;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Stepapo\Utils\Model\Collection;
use Webovac\Core\Control\MenuItem\MenuItemTemplate;
use Webovac\Core\Model\CmsEntity;


class NavigationTemplate extends BaseTemplate
{
	public ?string $title;
	public WebData $webData;
	public LayoutData $layoutData;
	public PageData $activePageData;
	public LanguageData $languageData;
	public DataModel $dataModel;
	public ?CmsEntity $entity;
	/** @var Collection<PageData> */ public Collection $pageDatas;
	/** @var IEntity[] */ public array $entityMenuItems;


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
			context: 'secondary',
			presenter: $this->presenter,
		));
	}
}
