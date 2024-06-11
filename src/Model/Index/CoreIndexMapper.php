<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Index;

use App\Model\Language\LanguageRepository;


trait CoreIndexMapper
{
	public function filterByText(string $text)
	{
		$languages = $this->getRepository()->getModel()->getRepository(LanguageRepository::class)->findAll()->fetchPairs('id', 'name');
		$where = [];
		$rank = [];
		$args = [];
		foreach ($languages as $id => $name) {
			$where[] = "(((translations.language_id = %i)) AND (translations.document @@ to_tsquery(%s, %s)))";
			$args = array_merge($args, [$id, $name, $text]);
		}
		foreach ($languages as $id => $name) {
			$rank[] = "WHEN translations.language_id = %i THEN ts_rank(translations.document, to_tsquery(%s, %s))";
			$args = array_merge($args, [$id, $name, $text]);
		}
		return $this->toCollection(
			$this->connection->query('
				SELECT index.*, rank
				FROM (
					SELECT index.id, CASE ' . implode(' ', $rank) . ' END AS rank
					FROM index
					LEFT JOIN index_translation AS translations ON (index.id = translations.index_id)
					WHERE ' . implode(' OR ', $where) . '
				) s
				LEFT JOIN index ON (s.id = index.id)
				GROUP BY index.id, rank
				ORDER BY rank DESC
			', ...$args)
		);
	}
}
