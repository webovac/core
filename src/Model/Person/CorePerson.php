<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Person;

use Build\Model\Language\LanguageData;
use Build\Model\PersonTranslation\PersonTranslation;
use Build\Model\Web\WebData;
use Nette\DI\Attributes\Inject;
use Stepapo\Model\Orm\AuditableTrait;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Model\File\HasFilesTrait;


/**
 * @property string $name {virtual}
 * @property string $nameForList {virtual}
 * @property string $title {virtual}
 * @property string|null $content {virtual}
 */
trait CorePerson
{
	use AuditableTrait;
	use HasFilesTrait;

	#[Inject] public DataProvider $dataProvider;


	protected function getterName(): ?string
	{
		return implode(' ', array_filter([$this->firstName, $this->lastName]));
	}


	protected function getterNameForList(): ?string
	{
		return implode(' ', array_filter([$this->lastName, $this->firstName]));
	}


	public function getterTitle(): string
	{
		return $this->name;
	}


	public function getterContent(): ?string
	{
		return $this->getTranslation($this->dataProvider->getLanguageData())?->content;
	}


	public function getTranslation(LanguageData $language): ?PersonTranslation
	{
		return $this->translations->toCollection()->getBy(['language' => $language->id]);
	}


	public function addWeb(WebData $webData): void
	{
		if (!$this->webs->has($webData->id)) {
			$this->webs->add($webData->id);
		}
	}


	public function hasTranslations(): bool
	{
		return $this->translations->countStored() > 0;
	}
}
