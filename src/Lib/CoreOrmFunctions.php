<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use App\Lib\OrmFunctions;
use App\Model\Orm;
use Nette\Utils\Strings;
use Nextras\Dbal\IConnection;
use Nextras\Dbal\Platforms\IPlatform;
use Nextras\Dbal\QueryBuilder\QueryBuilder;
use Nextras\Orm\Collection\Aggregations\Aggregator;
use Nextras\Orm\Collection\Functions\CollectionFunction;
use Nextras\Orm\Collection\Functions\Result\ArrayExpressionResult;
use Nextras\Orm\Collection\Functions\Result\DbalExpressionResult;
use Nextras\Orm\Collection\Helpers\ArrayCollectionHelper;
use Nextras\Orm\Collection\Helpers\DbalQueryBuilderHelper;
use Nextras\Orm\Entity\IEntity;


trait CoreOrmFunctions
{
	public const string LIKE_FILTER = 'likeFilter';
	public const string LIKE_FILTER_OR = 'likeFilterOr';
	public const string PERSON_FILTER = 'personFilter';
	public const string PERSON_ORDER = 'personOrder';


	public function __construct(
		private IConnection $connection,
		private Orm $orm,
	) {}


	public function call(string $name): CollectionFunction
	{
		return new class($name, $this->connection) implements CollectionFunction {
			public function __construct(private string $name, private IConnection $connection)
			{}

			public function processDbalExpression(
				DbalQueryBuilderHelper $helper,
				QueryBuilder $builder,
				array $args,
				?Aggregator $aggregator = null,
			): DbalExpressionResult
			{
				return OrmFunctions::{$this->name}($this->connection->getPlatform(), $helper, $builder, $args, $aggregator);
			}

			public function processArrayExpression(
				ArrayCollectionHelper $helper,
				IEntity $entity,
				array $args,
				?Aggregator $aggregator = null,
			): ArrayExpressionResult
			{
				return new ArrayExpressionResult(null);
			}
		};
	}


	public static function likeFilter(IPlatform $platform, DbalQueryBuilderHelper $helper, QueryBuilder $builder, array $args, ?Aggregator $aggregator): DbalExpressionResult
	{
		assert(count($args) === 2 && is_string($args[0]) && is_string($args[1]));
		$column = $helper->processExpression($builder, $args[0], $aggregator)->args[0];
		return new DbalExpressionResult('LOWER(%column) LIKE %_like_', [$column, Strings::lower($args[1])]);
	}


	public static function likeFilterOr(IPlatform $platform, DbalQueryBuilderHelper $helper, QueryBuilder $builder, array $args, ?Aggregator $aggregator): DbalExpressionResult
	{
		assert(count($args) === 3 && is_string($args[0]) && is_string($args[1]) && is_string($args[2]));
		$column1 = $helper->processExpression($builder, $args[0], $aggregator)->args[0];
		$column2 = $helper->processExpression($builder, $args[1], $aggregator)->args[0];
		return new DbalExpressionResult('%column LIKE %_like_ OR %column LIKE %_like_', [$column1, $args[2], $column2, $args[2]]);
	}


	public static function personFilter(IPlatform $platform, DbalQueryBuilderHelper $helper, QueryBuilder $builder, array $args, ?Aggregator $aggregator): DbalExpressionResult
	{
		assert(count($args) === 3 && is_string($args[0]) && is_string($args[1]) && is_string($args[2]));
		$lastNameColumn = $helper->processExpression($builder, $args[1], $aggregator)->args[0];
		$firstNameColumn = $helper->processExpression($builder, $args[0], $aggregator)->args[0];
		return new DbalExpressionResult((
			$platform->getName() === 'pgsql'
				? "LOWER(%column || ' ' || %column)"
				: "LOWER(CONCAT(%column, ' ', %column))"
			) . " LIKE %_like_ OR " . (
			$platform->getName() === 'pgsql'
				? "LOWER(%column || ' ' || %column)"
				: "LOWER(CONCAT(%column, ' ', %column))"
			) . " LIKE %_like_",
			[$lastNameColumn, $firstNameColumn, Strings::lower($args[2]), $firstNameColumn, $lastNameColumn, Strings::lower($args[2])
		]);
	}


	public static function personOrder(IPlatform $platform, DbalQueryBuilderHelper $helper, QueryBuilder $builder, array $args, ?Aggregator $aggregator): DbalExpressionResult
	{
		$lastNameColumn = $helper->processExpression($builder, $args[1], $aggregator)->args[0];
		$firstNameColumn = $helper->processExpression($builder, $args[0], $aggregator)->args[0];
		return new DbalExpressionResult('%column || %column', [$lastNameColumn, $firstNameColumn]);
	}
}
