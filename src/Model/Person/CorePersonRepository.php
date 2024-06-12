<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Person;

use App\Model\Person\Person;
use App\Model\Person\PersonData;


trait CorePersonRepository
{
	public function getByData(PersonData|string $data): ?Person
	{
		return $this->getBy(['email' => $data instanceof PersonData ? $data->email : $data]);
	}
}
