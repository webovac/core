<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Person;

use Build\Model\Person\Person;
use Build\Model\Person\PersonData;
use Build\Model\Web\WebData;
use Nextras\Orm\Collection\ICollection;


trait CorePersonRepository
{
	public function getByData(PersonData|string $data): ?Person
	{
		if (method_exists($this, 'getByDataCustom')) {
			return $this->getByDataCustom($data);
		}
		return $this->getBy(['email' => $data instanceof PersonData ? $data->email : $data]);
	}


	public function getFilterByWeb(WebData $webData): array
	{
		return [
			ICollection::OR,
			'webs->id' => $webData->id,
		];
	}
}
