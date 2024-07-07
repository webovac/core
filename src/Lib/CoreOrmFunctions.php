<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use App\Lib\OrmFunctions;
use App\Model\Orm;
use Nette\Utils\Strings;
use Nextras\Dbal\IConnection;
use Nextras\Dbal\Platforms\IPlatform;
use Nextras\Dbal\QueryBuilder\QueryBuilder;
use Nextras\Orm\Collection\Functions\IQueryBuilderFunction;
use Nextras\Orm\Collection\Helpers\DbalExpressionResult;
use Nextras\Orm\Collection\Helpers\DbalQueryBuilderHelper;


trait CoreOrmFunctions
{
	public const LIKE_FILTER = 'likeFilter';
	public const PERSON_FILTER = 'personFilter';


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
			($platform->getName() === 'pgsql'
				? "LOWER(last_name || ' ' || first_name)"
				: "LOWER(CONCAT(last_name, ' ', first_name)")
				. "LIKE %_like_",
			Strings::lower($args[0]),
		]);
	}
}
