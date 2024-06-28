<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Text;

use App\Model\Language\LanguageData;
use App\Model\Person\Person;
use App\Model\TextTranslation\TextTranslation;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Relationships\OneHasMany;


/**
 * @property int $id {primary}
 *
 * @property string $name
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 *
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 *
 * @property OneHasMany|TextTranslation[] $translations {1:m TextTranslation::$text, orderBy=language->rank}
 */
trait CoreText
{
	public function getTranslation(LanguageData $language): ?TextTranslation
	{
		return $this->translations->toCollection()->getBy(['language' => $language->id]);
	}
}
