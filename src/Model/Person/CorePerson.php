<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Person;

use App\Model\Log\Log;
use App\Model\Preference\Preference;
use App\Model\Role\Role;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\OneHasMany;
use Webovac\Core\IndexDefinition;
use Webovac\Core\IndexTranslationDefinition;


/**
 * @property int $id {primary}
 *
 * @property string|null $email
 * @property string $firstName
 * @property string $lastName
 * @property string $name {virtual}
 * @property string $nameForList {virtual}
 * @property string $title {virtual}
 *
 * @property OneHasMany|Preference[] $preferences {1:m Preference::$person}
 * @property ManyHasMany|Role[] $roles {m:m Role::$persons}
 *
 * @property DateTimeImmutable|null $lastLoginAt
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
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


	public function getIndexDefinition(): IndexDefinition
	{
		$definition = new IndexDefinition;
		$definition->entity = $this;
		$definition->entityName = 'person';
		$translationDefinition = new IndexTranslationDefinition;
		$translationDefinition->documents = ['A' => $this->name];
		$definition->translations[] = $translationDefinition;
		return $definition;
	}


	public function createLog(string $type): ?Log
	{
		$log = new Log;
		$log->person = $this;
		$log->type = $type;
		$log->createdByPerson = $this;
		$log->date = match($type) {
			Log::TYPE_CREATE => $this->createdAt,
			Log::TYPE_UPDATE => $this->updatedAt,
		};
		return $log;
	}
}
