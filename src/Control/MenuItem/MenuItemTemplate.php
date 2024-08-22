<?php

declare(strict_types=1);

namespace Webovac\Core\Control\MenuItem;

use App\Control\BaseTemplate;
use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslationData;
use App\Model\Web\WebData;
use Latte\Engine;
use Nette\Application\UI\Presenter;
use Webovac\Core\Model\CmsEntity;


class MenuItemTemplate extends BaseTemplate
{
	public function __construct(
		public Engine $latte,
		public WebData $webData,
		public PageData $pageData,
		public ?PageTranslationData $pageTranslationData,
		public LanguageData $languageData,
		public LanguageData $targetLanguageData,
		public ?CmsEntity $entity,
		public ?CmsEntity $linkedEntity,
		public DataModel $dataModel,
		public string $context,
		public Presenter $presenter,
	) {
		parent::__construct($latte);
	}
}