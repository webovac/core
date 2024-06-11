<?php

declare(strict_types=1);

namespace Webovac\Core\Model\WebTranslation;

use App\Model\Language\Language;
use App\Model\Person\Person;
use App\Model\Web\Web;
use Nextras\Dbal\Utils\DateTimeImmutable;


/**
 * @property int $id {primary}
 *
 * @property string $title
 * @property string|null $footer
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 *
 * @property Web $web {m:1 Web::$translations}
 * @property Language $language {m:1 Language, oneSided=true}
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 */
trait CoreWebTranslation
{
	public function onAfterPersist(): void
	{
		parent::onAfterPersist();
		$this->getRepository()->getMapper()->createIndexTranslation($this);
	}
}
