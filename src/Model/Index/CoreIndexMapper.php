<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Index;

use App\Model\Language\LanguageRepository;
use Nextras\Dbal\Platforms\MySqlPlatform;
use Nextras\Dbal\Platforms\PostgreSqlPlatform;


trait CoreIndexMapper
{
	public function filterByText(string $text)
	{

		if ($this->connection->getPlatform() instanceof PostgreSqlPlatform) {
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
					SELECT "index".*, "rank"
					FROM (
						SELECT "index"."id", CASE ' . implode(' ', $rank) . ' END AS "rank"
						FROM "index"
						LEFT JOIN "index_translation" AS "translations" ON ("index"."id" = "translations"."index_id")
						WHERE ' . implode(' OR ', $where) . '
					) "s"
					LEFT JOIN "index" ON ("s"."id" = "index"."id")
					GROUP BY "index"."id", "rank"
					ORDER BY "rank" DESC
				', ...$args)
			);
		} elseif ($this->connection->getPlatform() instanceof MySqlPlatform) {
			return $this->toCollection(
				$this->connection->query('
					SELECT `index`.*, `match_a`, `match_b`, `match_c`, `match_d`, `match_e`
					FROM (
						SELECT `index`.`id`, 
							   IF(ISNULL(`document_a`), 0, MATCH (`document_a`) AGAINST (%s IN BOOLEAN MODE)) AS match_a, 
							   IF(ISNULL(`document_b`), 0, MATCH (`document_b`) AGAINST (%s IN BOOLEAN MODE)) AS match_b,
							   IF(ISNULL(`document_c`), 0, MATCH (`document_c`) AGAINST (%s IN BOOLEAN MODE)) AS match_c,
							   IF(ISNULL(`document_d`), 0, MATCH (`document_d`) AGAINST (%s IN BOOLEAN MODE)) AS match_d,
							   IF(ISNULL(`document_e`), 0, MATCH (`document_e`) AGAINST (%s IN BOOLEAN MODE)) AS match_e
						FROM `index`
						LEFT JOIN `index_translation` ON (`index`.`id` = `index_translation`.`index_id`)
						WHERE MATCH(`document`) AGAINST (%s IN BOOLEAN MODE)
					) s
					LEFT JOIN `index` ON (s.id = `index`.id)
					GROUP BY `index`.id
					ORDER BY `match_a` * 16 + `match_b` * 8 + `match_c` * 4 + `match_d` * 2 + `match_e` DESC
				', $text, $text, $text, $text, $text, $text)
			);
		}
	}
}
