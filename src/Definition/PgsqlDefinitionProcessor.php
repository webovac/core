<?php

declare(strict_types=1);

namespace Webovac\Core\Definition;

use Nextras\Dbal\Connection;
use Nextras\Dbal\QueryException;


class PgsqlDefinitionProcessor implements DefinitionProcessor
{
	private string $defaultSchema = 'public';
	private Definition $definition;
	private int $count = 0;
	private array $createSequence = [];
	private array $createTable = [];
	private array $alterTable = [];
	private array $createIndex = [];


	public function __construct(
		private Connection $dbal,
	) {}


	/**
	 * @throws QueryException
	 */
	public function process(Definition $structure): int
	{
		$this->definition = $structure;
		$this->prepare();
		foreach ($this->createSequence as $createSequence) {
			$this->dbal->query($createSequence);
			$this->count++;
		}
		foreach ($this->createTable as $createTable) {
			$this->dbal->query($createTable);
			$this->count++;
		}
		foreach ($this->alterTable as $alterTable) {
			$this->dbal->query($alterTable);
			$this->count++;
		}
		foreach ($this->createIndex as $createIndex) {
			$this->dbal->query($createIndex);
			$this->count++;
		}
		return $this->count;
	}


	private function prepare(): void
	{
		$this->reset();
		foreach ($this->definition->tables as $table) {
			if ($table->type === 'create') {
				$this->addCreateTable($table);
			} elseif ($table->type === 'alter') {
				$this->alterTable($table);
			}
		}
	}


	private function addCreateSequence(Table $table): void
	{
		$this->createSequence[] = "CREATE SEQUENCE \"{$table->name}_id_seq\";";
	}


	private function addCreateTable(Table $table): void
	{
		$schema = $table->schema ?: $this->defaultSchema;
		$t = [];
		$t['create'] = "CREATE TABLE \"$schema\".\"$table->name\" (";
		$c = [];
		foreach ($table->columns as $column) {
			$c[] = $this->column($table, $column);
		}
		$c[] = $this->primary($table->primaryKey);
		foreach ($table->uniqueKeys as $uniqueKey) {
			$c[] = $this->unique($table, $uniqueKey);
		}
		$t['columns'] = implode(', ', $c);
		$t['end'] = ');';
		$this->createTable[] = implode(' ', $t);
		foreach ($table->indexes as $index) {
			$this->addCreateIndex($table, $index);
		}
		foreach ($table->foreignKeys as $foreignKey) {
			$this->addAlterTableWithForeignKey($table, $foreignKey);
		}
	}


	private function alterTable(Table $table): void
	{
		foreach ($table->columns as $column) {
			$this->addAlterTableWithColumn($table, $column);
		}
		foreach ($table->uniqueKeys as $uniqueKey) {
			$this->addAlterTableWithUnique($table, $uniqueKey);
		}
		foreach ($table->indexes as $index) {
			$this->addCreateIndex($table, $index);
		}
		foreach ($table->foreignKeys as $foreignKey) {
			$this->addAlterTableWithForeignKey($table, $foreignKey);
		}
	}


	private function addCreateIndex(Table $table, Key $key): void
	{
		$schema = $table->schema ?: $this->defaultSchema;
		$c = [];
		foreach ($key->columns as $column) {
			$c[] = "\"$column\"";
		}
		$this->createIndex[] = "CREATE INDEX ON \"$schema\".\"$table->name\" (" . implode(", ", $c) . ");";
	}


	private function addCreateFulltext(Table $table, Column $column): void
	{
		$schema = $table->schema ?: $this->defaultSchema;
		$this->createIndex[] = "CREATE INDEX ON \"$schema\".\"$table->name\" USING gin(\"$column->name\");";
	}


	private function addAlterTableWithColumn(Table $table, Column $column): void
	{
		$schema = $table->schema ?: $this->defaultSchema;
		$this->alterTable[] = "ALTER TABLE \"$schema\".\"$table->name\" ADD COLUMN " . $this->column($table, $column) . ";";
	}


	private function addAlterTableWithUnique(Table $table, Key $key): void
	{
		$schema = $table->schema ?: $this->defaultSchema;
		$this->alterTable[] = "ALTER TABLE \"$schema\".\"$table->name\" ADD " . $this->unique($table, $key) . ";";
	}


	private function addAlterTableWithForeignKey(Table $table, ForeignKey $foreignKey): void
	{
		$schema = $table->schema ?: $this->defaultSchema;
		$foreignSchema = $foreignKey->schema ?: $this->defaultSchema;
		$k = [];
		$k['alter'] = "ALTER TABLE \"$schema\".\"$table->name\"";
		$k['constraint'] = "ADD CONSTRAINT \"{$table->name}_$foreignKey->name\"";
		$k['foreignKey'] = "FOREIGN KEY (\"$foreignKey->name\")";
		$k['references'] = "REFERENCES \"$foreignSchema\".\"$foreignKey->table\" (\"$foreignKey->column\")";
		$k['onDelete'] = "ON DELETE " . strtoupper($foreignKey->onDelete);
		$k['onUpdate'] = "ON UPDATE " . strtoupper($foreignKey->onUpdate);
		$this->alterTable[] = implode(' ', $k);
	}


	private function column(Table $table, Column $column): string
	{
		$c = [];
		$c['name'] = "\"$column->name\"";
		$c['type'] = $this->getType($column->type);
		if ($column->type === 'fulltext') {
			$this->addCreateFulltext($table, $column);
		}
		if (!$column->null) {
			$c['null'] = 'NOT NULL';
		}
		if ($column->default) {
			$c['default'] = "DEFAULT " . $this->getDefault($column->default, $column->type);
		}
		if ($column->auto) {
			$this->addCreateSequence($table);
			$c['auto'] = "DEFAULT nextval('{$table->name}_id_seq'::regclass)";
		}
		return implode(' ', $c);
	}


	private function primary(Key $key): string
	{
		$c = [];
		foreach ($key->columns as $column) {
			$c[] = "\"$column\"";
		}
		return "PRIMARY KEY (" . implode(", ", $c) . ")";
	}


	private function unique(Table $table, Key $key): string
	{
		$c = [];
		foreach ($key->columns as $column) {
			$c[] = "\"$column\"";
		}
		return "UNIQUE (" . implode(", ", $c) . ")";
	}


	private function getType(string $type): string
	{
		return match($type) {
			'bool' => 'bool',
			'int' => 'int4',
			'bigint' => 'int4',
			'string' => 'varchar',
			'text' => 'text',
			'datetime' => 'timestamp',
			'float' => 'numeric',
			'fulltext' => 'tsvector',
		};
	}


	private function getDefault(mixed $default, string $type): mixed
	{
		return match($default) {
			'now' => "now()",
			default => match($type) {
				'bool' => $default ? 'true' : 'false',
				'int' => $default,
				'bigint' => $default,
				'string' => "'$default'",
				'text' => "'$default'",
				'datetime' => "$default",
			}
		};
	}


	private function reset(): void
	{
		$this->count = 0;
		$this->createSequence = [];
		$this->createTable = [];
		$this->createIndex = [];
		$this->alterTable = [];
	}
}