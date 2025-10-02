<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use Build\Model\Page\Page;
use Nextras\Dbal\QueryException;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Mapper\Dbal\DbalMapper;


trait CorePageMapper
{
	public function getManyHasManyParameters(PropertyMetadata $sourceProperty, DbalMapper $targetMapper): array
	{
		if ($sourceProperty->name === 'authorizedPersons') {
			return [
				'page2authorized_person',
				['page_id', 'person_id'],
			];
		}
		if ($sourceProperty->name === 'authorizedRoles') {
			return [
				'page2authorized_role',
				['page_id', 'role_id'],
			];
		}

		return parent::getManyHasManyParameters($sourceProperty, $targetMapper);
	}


	/**
	 * @throws QueryException
	 */
	public function movePage(Page $movedPage, ?Page $oldParentPage, int $oldRank, ?Page $newParentPage, int $newRank): void
	{
		$filter = $movedPage->web
			? ['web_id' => $movedPage->web->id]
			: ['module_id' => $movedPage->module->id, 'web_id' => null];

		if ($oldParentPage === $newParentPage) {
			if ($newRank < $oldRank) {
				$this->connection->query("
					UPDATE page
					SET rank = rank + 1
					WHERE %and;
				", [
					'parent_page_id' => $newParentPage?->id,
					['id <> %i', $movedPage->id],
					['rank >= %i', $newRank],
					['rank < %i', $oldRank],
				] + $filter);
			} elseif ($newRank > $oldRank) {
				$this->connection->query("
					UPDATE page
					SET rank = rank - 1
					WHERE %and;
				", [
					'parent_page_id' => $newParentPage?->id,
					['id <> %i', $movedPage->id],
					['rank > %i', $oldRank],
					['rank <= %i', $newRank],
				] + $filter);
			}
		} else {
			$this->connection->query("
				UPDATE page
				SET rank = rank + 1
				WHERE %and;
			", [
				'parent_page_id' => $newParentPage?->id,
				['rank >= %i', $newRank],
			] + $filter);

			$this->connection->query("
				UPDATE page
				SET rank = rank - 1
				WHERE %and;
			", [
				'parent_page_id' => $oldParentPage?->id,
				['rank > %i', $oldRank],
			] + $filter);
		}

		$movedPage->parentPage = $newParentPage;
		$movedPage->rank = $newRank;
		$this->getRepository()->persist($movedPage);
	}


	/**
	 * @throws QueryException
	 */
	public function removePage(Page $page): void
	{
		$this->getDatabasePlatform();
		$filter = $page->web
			? ['web_id' => $page->web->id]
			: ['module_id' => $page->module->id, 'web_id' => null];

		$this->connection->query("
			UPDATE page
			SET rank = rank - 1
			WHERE %and;
		", [
			'parent_page_id' => $page->parentPage?->id,
			['rank > %i', $page->rank],
		] + $filter);
		$this->connection->query('DELETE FROM %table WHERE id = %i', $this->getTableName(), $page->getPersistedId());
	}
}
