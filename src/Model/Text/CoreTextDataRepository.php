<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Text;

use Build\Model\Text\Text;
use Build\Model\TextTranslation\TextTranslationDataRepository;
use Nette\DI\Attributes\Inject;
use Nextras\Orm\Entity\IEntity;


trait CoreTextDataRepository
{
	#[Inject] public TextTranslationDataRepository $textTranslationDataRepository;


	protected function getIdentifier(IEntity $entity): mixed
	{
		assert($entity instanceof Text);
		return $entity->name;
	}
}