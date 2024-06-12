<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use App\Lib\OrmFunctions;
use Nette\Utils\Strings;
use Nextras\Dbal\Drivers\IDriver;
use Nextras\Dbal\IConnection;
use Nextras\Dbal\Platforms\IPlatform;
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
		assert(count($args) === 4 && is_string($args[0]) && is_string($args[1]) && is_string($args[2]) && is_array($args[3]));
		$column = $helper->processPropertyExpr($builder, $args[0])->args[1];
		$builder
			->addGroupBy('%column', $column);
		return new DbalExpressionResult(['%column @@ to_tsquery(%s, %s)', $column, $args[2], $args[1]]);
	}


	public static function fulltextOrder(IPlatform $platform, DbalQueryBuilderHelper $helper, QueryBuilder $builder, array $args): DbalExpressionResult
	{
		assert(count($args) === 3 && is_string($args[0]) && is_string($args[1]) && is_array($args[2]));
		$column = $helper->processPropertyExpr($builder, $args[0])->args[1];
		$orderBy = 'CASE ';
		$a = [];
		foreach ($args[2] as $id => $text) {
			$select .= 'WHEN %column = %i THEN ts_rank(%column, to_tsquery(%s, %s)) ';
			$a = array_merge($a, ['language_id', $id, $column, $text, $args[1]]);
		}
		$select .= 'END';
		$builder
			->addSelect($select, ...$a)
			->addGroupBy('%column', $column);
		return new DbalExpressionResult(['rank']);
	}
}
