<?php

declare(strict_types=1);

namespace Webovac\Core\Model\LanguageTranslation;

use App\Model\Language\Language;
use App\Model\Person\Person;
use Nextras\Dbal\Utils\DateTimeImmutable;


/**
 * @property int $id {primary}
 *
 * @property string $title
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 *
 * @property Language $language {m:1 Language::$translations}
 * @property Language|null $translationLanguage {m:1 Language, oneSided=true}
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 */
trait CoreLanguageTranslation
{
	public function onAfterPersist(): void
	{
		parent::onAfterPersist();
		$this->getRepository()->getMapper()->createIndexTranslation($this);
	}
}
