<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Preference;

use Build\Model\Person\Person;
use Build\Model\Preference\Preference;
use Build\Model\Web\WebData;
use Build\Model\Web\WebRepository;


trait CorePreferenceRepository
{
	public function getPreference(WebData $webData, Person $person): ?Preference
	{
		return $this->getBy([
			'web' => $webData->id,
			'person' => $person,
		]);
	}


	public function setPreference(WebData $webData, Person $person, array $data): void
	{
		$preference = $this->getPreference($webData, $person);
		if (!$preference) {
			$preference = new Preference;
			$preference->web = $this->getModel()->getRepository(WebRepository::class)->getById($webData->id);
			$preference->person = $person;
		}
		foreach ($data as $key => $value) {
			$preference->$key = $value;
		}
		$this->persistAndFlush($preference);
	}
}
