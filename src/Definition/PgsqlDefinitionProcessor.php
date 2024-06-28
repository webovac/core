<?php

namespace Webovac\Core\Definition;

use Nextras\Dbal\Connection;


class PgsqlDefinitionProcessor implements DefinitionProcessor
{
	private string $defaultSchema = 'public';
	private Definition $definition;
	private int $count = 0;
	private array $createSequence = [];
	private array $createTable = [];
	private array $createIndex = [];
	private array $alterTable = [];


	public function __construct(
		private Connection $dbal,
	) {}


	public function process(Definition $structure): int
	{
		$this->definition = $structure;
		$this->prepare();
		foreach ($this->createSequence as $createSequence) {
//			Dumper::dump($createSequence);
			$this->dbal->query($createSequence);
			$this->count++;
		}
		foreach ($this->createTable as $createTable) {
//			Dumper::dump($createTable);
			$this->dbal->query($createTable);
			$this->count++;
		}
		foreach ($this->createIndex as $createIndex) {
//			Dumper::dump($createIndex);
			$this->dbal->query($createIndex);
			$this->count++;
		}
		foreach ($this->alterTable as $alterTable) {
//			Dumper::dump($alterTable);
			$this->dbal->query($alterTable);
			$this->count++;
		}
		return $this->count;
	}


	private function prepare(): void
	{
		$this->reset();
		foreach ($this->definition->tables as $table) {
			$this->addCreateTable($table);
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
			$c[] = $this->unique($uniqueKey);
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


	private function addCreateIndex(Table $table, KeyConfig $key): void
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


	private function addAlterTableWithForeignKey(Table $table, ForeignKey $foreignKey): void
	{
		$schema = $table->schema ?: $this->defaultSchema;
		$k = [];
		$k['alter'] = "ALTER TABLE \"$schema\".\"$table->name\"";
		$k['constraint'] = "ADD CONSTRAINT \"{$table->name}_{$foreignKey->name}\"";
		$k['foreignKey'] = "FOREIGN KEY (\"$foreignKey->name\")";
		$k['references'] = "REFERENCES \"$schema\".\"$foreignKey->table\" (\"$foreignKey->column\")";
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


	private function primary(KeyConfig $key)
	{
		$c = [];
		foreach ($key->columns as $column) {
			$c[] = "\"$column\"";
		}
		return "PRIMARY KEY (" . implode(", ", $c) . ")";
	}


	private function unique(KeyConfig $key)
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
			'string' => 'varchar',
			'text' => 'text',
			'datetime' => 'timestamp',
			'float' => 'numeric',
			'fulltext' => 'tsvector',
		};
	}


	private function getDefault(string $default, string $type): string
	{
		return match($default) {
			'now' => "now()",
			default => match($type) {
				'bool' => $default ? 'true' : 'false',
				'int' => $default,
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