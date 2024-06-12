<?php

declare(strict_types=1);

namespace Webovac\Core\Model\ModuleTranslation;

use App\Model\IndexTranslation\IndexTranslationRepository;
use App\Model\Language\Language;
use App\Model\Module\Module;
use App\Model\Person\Person;
use Nextras\Dbal\Utils\DateTimeImmutable;


/**
 * @property int $id {primary}
 *
 * @property string $title
 * @property string|null $description
 * @property string|null $basePath
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 *
 * @property Module $module {m:1 Module::$translations}
 * @property Language $language {m:1 Language, oneSided=true}
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 */
trait CoreModuleTranslation
{
	public function onAfterPersist(): void
	{
		parent::onAfterPersist();
		$this->getRepository()->getModel()->getRepository(IndexTranslationRepository::class)->createIndexTranslation(
			$this->module,
			'module',
			$this->language,
			['A' => $this->module->name, 'B' => $this->title, 'C' => $this->description],
		);
	}
}
