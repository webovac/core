<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use App\Lib\OrmFunctions;
use App\Model\Orm;
use Nette\Utils\Strings;
use Nextras\Dbal\Drivers\IDriver;
use Nextras\Dbal\IConnection;
use Nextras\Dbal\Platforms\IPlatform;
use Nextras\Dbal\Platforms\PostgreSqlPlatform;
use Nextras\Dbal\QueryBuilder\QueryBuilder;
use Nextras\Orm\Collection\Functions\IQueryBuilderFunction;
use Nextras\Orm\Collection\Helpers\DbalExpressionResult;
use Nextras\Orm\Collection\Helpers\DbalQueryBuilderHelper;
use Nextras\Orm\Collection\ICollection;


trait CoreOrmFunctions
{
	public const LIKE_FILTER = 'likeFilter';
	public const PERSON_FILTER = 'personFilter';
	public const FULLTEXT_FILTER = 'fulltextFilter';
	public const FULLTEXT_ORDER = 'fulltextOrder';


	public function __construct(
		private IConnection $connection,
		private Orm $orm,
	) {}


	public function call(string $name)
	{
		return new class($name, $this->connection) implements IQueryBuilderFunction {
			public function __construct(private string $name, private IConnection $connection)
			{}

			public function processQueryBuilderExpression(DbalQueryBuilderHelper $helper, QueryBuilder $builder, array $args): DbalExpressionResult
			{
				return OrmFunctions::{$this->name}($this->connection->getPlatform(), $helper, $builder, $args);
			}
		};
	}


	public static function likeFilter(IPlatform $platform, DbalQueryBuilderHelper $helper, QueryBuilder $builder, array $args): DbalExpressionResult
	{
		assert(count($args) === 2 && is_string($args[0]) && is_string($args[1]));
		$column = $helper->processPropertyExpr($builder, $args[0])->args[1];
		return new DbalExpressionResult(['LOWER(%column) LIKE %_like_', $column, Strings::lower($args[1])]);
	}


	public static function personFilter(IPlatform $platform, DbalQueryBuilderHelper $helper, QueryBuilder $builder, array $args): DbalExpressionResult
	{
		assert(count($args) === 1 && is_string($args[0]));
		return new DbalExpressionResult([
			$platform->getName() === 'pgsql' ? "LOWER(last_name || ' ' || first_name) LIKE %_like_" : "CONCAT(last_name, ' ', first_name) LIKE %_like_",
			Strings::lower($args[0]),
		]);
	}


	public static function fulltextFilter(IPlatform $platform, DbalQueryBuilderHelper $helper, QueryBuilder $builder, array $args): DbalExpressionResult
	{
		assert(count($args) === 3 && is_string($args[0]) && is_string($args[1]) && is_string($args[2]) && is_array($args[3]));
		$documentColumn = $helper->processPropertyExpr($builder, $args[0])->args[1];
		$languageColumn = $helper->processPropertyExpr($builder, $args[2])->args[1];
		if ($platform instanceof PostgreSqlPlatform) {
			$where = [];
			$a = [];
			foreach ($args[3] as $id => $name) {
				$where[] = "(((%column = %i)) AND (%column @@ to_tsquery(%s, unaccent(%s))))";
				$a = array_merge($a, [$languageColumn, $id, $documentColumn, $name, $args[1]]);
			}
			return new DbalExpressionResult(array_merge([implode(' OR ', $where)], $a));
		} else {
			return new DbalExpressionResult(["MATCH(%column) AGAINST (%s IN BOOLEAN MODE)", $documentColumn, $args[1]]);
		}
	}


	public static function fulltextOrder(IPlatform $platform, DbalQueryBuilderHelper $helper, QueryBuilder $builder, array $args): DbalExpressionResult
	{
		assert(count($args) === 3 && is_string($args[0]) && is_string($args[1]) && is_string($args[2]) && is_array($args[3]));

		$languageColumn = $helper->processPropertyExpr($builder, $args[2])->args[1];
		if ($platform instanceof PostgreSqlPlatform) {
			$documentColumn = $helper->processPropertyExpr($builder, $args[0])->args[1];
			$select[] = "CASE";
			$a = [];
			foreach ($args[3] as $id => $text) {
				$select[] = "WHEN %column = %i THEN ts_rank(%column, to_tsquery(%s, %s))";
				$a = array_merge($a, [$languageColumn, $id, $documentColumn, $text, $args[1]]);
			}
			$select[] = "END AS \"rank\"";
			$builder
				->addSelect(implode(' ', $select), ...$a)
				->addGroupBy('%column', 'rank');
		} else {
			$aColumn = $helper->processPropertyExpr($builder, $args[0] . 'A')->args[1];
			$bColumn = $helper->processPropertyExpr($builder, $args[0] . 'B')->args[1];
			$cColumn = $helper->processPropertyExpr($builder, $args[0] . 'C')->args[1];
			$dColumn = $helper->processPropertyExpr($builder, $args[0] . 'D')->args[1];
			$eColumn = $helper->processPropertyExpr($builder, $args[0] . 'E')->args[1];
			$select = "16 * IF(ISNULL(%column), 0, MATCH (%column) AGAINST (%s IN BOOLEAN MODE)) + 
						8 * IF(ISNULL(%column), 0, MATCH (%column) AGAINST (%s IN BOOLEAN MODE)) +
						4 * IF(ISNULL(%column), 0, MATCH (%column) AGAINST (%s IN BOOLEAN MODE)) +
						2 * IF(ISNULL(%column), 0, MATCH (%column) AGAINST (%s IN BOOLEAN MODE)) +
							IF(ISNULL(%column), 0, MATCH (%column) AGAINST (%s IN BOOLEAN MODE)) AS `rank`";
			$builder->addSelect(
				$select,
				$aColumn, $aColumn, $args[1],
				$bColumn, $bColumn, $args[1],
				$cColumn, $cColumn, $args[1],
				$dColumn, $dColumn, $args[1],
				$eColumn, $eColumn, $args[1],
			);
		}
		return new DbalExpressionResult(['rank']);
	}
}
