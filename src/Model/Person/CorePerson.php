<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Person;

use Build\Model\Web\WebData;


/**
 * @property string $name {virtual}
 * @property string $nameForList {virtual}
 * @property string $title {virtual}
 */
trait CorePerson
{
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


	public function addWeb(WebData $webData): void
	{
		if (!$this->webs->has($webData->id)) {
			$this->webs->add($webData->id);
		}
	}
}
