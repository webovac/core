<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;

use App\Model\Language\LanguageData;
use App\Model\LanguageTranslation\LanguageTranslation;
use App\Model\Person\Person;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Relationships\OneHasMany;


/**
 * @property int $id {primary}
 *
 * @property string $shortcut
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
}
