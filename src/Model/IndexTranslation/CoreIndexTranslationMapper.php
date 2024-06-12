<?php

declare(strict_types=1);

namespace Webovac\Core\Model\IndexTranslation;

use App\Model\Index\Index;
use App\Model\Index\IndexRepository;
use App\Model\IndexTranslation\IndexTranslation;
use App\Model\IndexTranslation\IndexTranslationRepository;
use App\Model\Language\Language;
use Nextras\Dbal\Platforms\MySqlPlatform;
use Nextras\Dbal\Platforms\PostgreSqlPlatform;
use Nextras\Orm\Entity\IEntity;


trait CoreIndexTranslationMapper
{
	public function createIndexTranslation(
		IEntity $indexEntity,
		string $indexEntityName,
		Language $language,
		array $documents,
	): void
	{
		$index = $this->getRepository()->getModel()->getRepository(IndexRepository::class)->getBy([
			$indexEntityName => $indexEntity
		]) ?: new Index;
		$index->$indexEntityName = $indexEntity;
		$this->getRepository()->getModel()->persist($index);
		$indexTranslation = $this->getRepository()->getModel()->getRepository(IndexTranslationRepository::class)->getBy([
			'index' => $index,
			'language' => $language
		]) ?: new IndexTranslation;
		$select = [];
		$args = [];
		$doc = [];
		$indexTranslation->index = $index;
		$indexTranslation->language = $language;
		foreach ($documents as $weight => $document) {
			if ($weight === 'A') {
				$select[] = "setweight(to_tsvector(COALESCE(%?s, ' ')), %s)";
				$args = array_merge($args, [$document, $weight]);
			} else {
				$select[] = "setweight(to_tsvector(%s, COALESCE(%?s, ' ')), %s)";
				$args = array_merge($args, [$language->name, $document, $weight]);
			}
			$doc[] = $document;
			$indexTranslation->{'document' . $weight} = $document;
		}

		if ($this->connection->getPlatform() instanceof PostgreSqlPlatform) {
			$select = $this->connection->query("SELECT " . implode(' || ', $select) . " AS document;", $args);
			$indexTranslation->document = $select->fetchField();
		} elseif ($this->connection->getPlatform() instanceof MySqlPlatform) {
			$indexTranslation->document = implode(' ', $doc);
		}
		$this->persist($indexTranslation);
	}
}
