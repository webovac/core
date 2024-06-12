<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;

use App\Model\Language\LanguageData;
use App\Model\LanguageTranslation\LanguageTranslation;
use App\Model\Person\Person;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Relationships\OneHasMany;
use Webovac\Cms\Control\LanguageItem\ILanguageItemControl;
use Webovac\Cms\Control\LanguageItem\LanguageItemControl;


/**
 * @property int $id {primary}
 *
 * @property string $shortcut
 * @property string $name
 * @property int $rank
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 *
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 *
 * @property OneHasMany|LanguageTranslation[] $translations {1:m LanguageTranslation::$language, orderBy=language->rank}
 */
trait CoreLanguage
{
	private ILanguageItemControl $component;


	public function getTranslation(LanguageData $language): ?LanguageTranslation
	{
		return $this->translations->toCollection()->getBy(['translationLanguage' => $language->id]);
	}


	public function getTitle(LanguageData $language): string
	{
		return $this->getTranslation($language)->title;
	}


	public function getParameter(?LanguageData $language = null): string
	{
		return $this->shortcut;
	}


	public function injectLanguageItem(ILanguageItemControl $component)
	{
		$this->component = $component;
	}


	public function getComponent(LanguageData $languageData, string $templateName): LanguageItemControl
	{
		return $this->component->create($this, $languageData, $templateName);
	}
}
