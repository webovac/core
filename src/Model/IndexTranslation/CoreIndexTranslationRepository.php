<?php

declare(strict_types=1);

namespace Webovac\Core\Model\IndexTranslation;

use App\Model\Language\Language;
use Nextras\Orm\Entity\IEntity;


trait CoreIndexTranslationRepository
{
	public function createIndexTranslation(
		IEntity $indexEntity,
		string $indexEntityName,
		Language $language,
		array $documents,
	): void
	{
		$this->getMapper()->createIndexTranslation($indexEntity, $indexEntityName, $language, $documents);
	}
}
