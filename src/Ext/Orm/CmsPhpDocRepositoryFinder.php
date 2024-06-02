<?php

declare(strict_types=1);

namespace Webovac\Core\Ext\Orm;

use Nette\Utils\Reflection;
use Nextras\Orm\Bridges\NetteDI\PhpDocRepositoryFinder;
use Nextras\Orm\Exception\InvalidStateException;
use Nextras\Orm\Exception\RuntimeException;
use Nextras\Orm\Model\Model;
use Nextras\Orm\Repository\IRepository;
use ReflectionClass;


class CmsPhpDocRepositoryFinder extends PhpDocRepositoryFinder
{
	/**
	 * @return array<string, string>
	 * @phpstan-param class-string<\Nextras\Orm\Model\IModel> $modelClass
	 * @phpstan-return array<string, class-string<IRepository>>
	 */
	public function findRepositories(string $modelClass): array
	{
		if ($modelClass === Model::class) {
			throw new InvalidStateException('Your model has to inherit from ' . Model::class . '. Use compiler extension configuration - model key.');
		}

		$modelReflection = new ReflectionClass($modelClass);
		$classFileName = $modelReflection->getFileName();
		assert($classFileName !== false);
		$this->builder->addDependency($classFileName);

		$repositories = [];

		foreach (array_merge($modelReflection->getTraits(), [$modelReflection]) as $reflection)
		{
			preg_match_all(
				'~^  [ \t*]*  @property(?:|-read)  [ \t]+  ([^\s$]+)  [ \t]+  \$  (\w+)  ()~mx',
				(string) $reflection->getDocComment(), $matches, PREG_SET_ORDER
			);

			/**
			 * @var string $type
			 * @var string $name
			 */
			foreach ($matches as [, $type, $name]) {
				/** @phpstan-var class-string<IRepository> $type */
				$type = Reflection::expandClassName($type, $reflection);
				if (!class_exists($type)) {
					throw new RuntimeException("Repository '{$type}' does not exist.");
				}

				$rc = new ReflectionClass($type);
				assert($rc->implementsInterface(IRepository::class), sprintf(
					'Property "%s" of class "%s" with type "%s" does not implement interface %s.',
					$modelClass, $name, $type, IRepository::class
				));

				$repositories[$name] = $type;
			}
		}

		return $repositories;
	}
}