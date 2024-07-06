<?php

namespace Webovac\Core\Definition;

use Nextras\Dbal\Connection;


class MysqlDefinitionProcessor implements DefinitionProcessor
{
	private string $defaultSchema = 'public';
	private Definition $definition;
	private int $count = 0;
	private array $createTable = [];
	private array $alterTable = [];
	private array $createIndex = [];


	public function __construct(
		private Connection $dbal,
	) {}


	public function process(Definition $structure): int
	{
		$this->definition = $structure;
		$this->prepare();
		$this->dbal->query("SET NAMES utf8mb4;");
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


	private function addCreateTable(Table $table): void
	{
		$schema = $table->schema ?: $this->defaultSchema;
		$t = [];
		$t['create'] = "CREATE TABLE `$schema`.`$table->name` (";
		$c = [];
		foreach ($table->columns as $column) {
			$c[] = $this->column($table, $column);
		}
		$c[] = $this->primary($table->primaryKey);
		foreach ($table->uniqueKeys as $uniqueKey) {
			$c[] = $this->unique($table, $uniqueKey);
		}
		$t['columns'] = implode(', ', $c);
		$t['end'] = ') ENGINE = InnoDB COLLATE = utf8mb4_unicode_520_ci;';
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
		$n = [];
		foreach ($key->columns as $column) {
			$c[] = "`$column`";
			$n[] = $column;
		}
		$this->createIndex[] = "CREATE INDEX `{$table->name}_" . implode("_", $n) . "_ix` ON `$schema`.`$table->name` (" . implode(", ", $c) . ");";
	}


	private function addCreateFulltext(Table $table, Column $column): void
	{
		$schema = $table->schema ?: $this->defaultSchema;
		$this->alterTable[] = "ALTER TABLE `$schema`.`$table->name` ADD FULLTEXT `{$table->name}_{$column->name}_fx` (`$column->name`);";
	}


	private function addAlterTableWithForeignKey(Table $table, ForeignKey $foreignKey): void
	{
		$schema = $table->schema ?: $this->defaultSchema;
		$k = [];
		$k['alter'] = "ALTER TABLE `$schema`.`$table->name`";
		$k['constraint'] = "ADD CONSTRAINT `{$table->name}_{$foreignKey->name}_fk`";
		$k['foreignKey'] = "FOREIGN KEY (`$foreignKey->name`)";
		$k['references'] = "REFERENCES `$schema`.`$foreignKey->table` (`$foreignKey->column`)";
		$k['onDelete'] = "ON DELETE " . strtoupper($foreignKey->onDelete);
		$k['onUpdate'] = "ON UPDATE " . strtoupper($foreignKey->onUpdate);
		$this->alterTable[] = implode(' ', $k);
	}


	private function addAlterTableWithColumn(Table $table, Column $column): void
	{
		$schema = $table->schema ?: $this->defaultSchema;
		$this->alterTable[] = "ALTER TABLE `$schema`.`$table->name` ADD COLUMN " . $this->column($table, $column) . ";";
	}


	private function addAlterTableWithUnique(Table $table, Key $key): void
	{
		$schema = $table->schema ?: $this->defaultSchema;
		$this->alterTable[] = "ALTER TABLE `$schema`.`$table->name` ADD " . $this->unique($table, $key) . ";";
	}


	private function column(Table $table, Column $column): string
	{
		$c = [];
		$c['name'] = "`$column->name`";
		$c['type'] = $this->getType($column->type);
		if ($column->type === 'fulltext') {
			$this->addCreateFulltext($table, $column);
		}
		if (in_array($column->type, ['string', 'text', 'fulltext'], true)) {
			$c['collate'] = 'COLLATE utf8mb4_unicode_520_ci';
		}
		$c['null'] = $column->null ? 'NULL' : 'NOT NULL';
		if ($column->default) {
			$c['default'] = "DEFAULT " . $this->getDefault($column->default, $column->type);
		}
		if ($column->auto) {
			$c['auto'] = "AUTO_INCREMENT";
		}
		return implode(' ', $c);
	}


	private function primary(Key $key)
	{
		$c = [];
		foreach ($key->columns as $column) {
			$c[] = "`$column`";
		}
		return "PRIMARY KEY (" . implode(", ", $c) . ")";
	}


	private function unique(Table $table, Key $key)
	{
		$c = [];
		$n = [];
		foreach ($key->columns as $column) {
			$c[] = "`$column`";
			$n[] = $column;
		}
		return "UNIQUE INDEX `{$table->name}_" . implode("_", $n) . "` (" . implode(", ", $c) . ")";
	}


	private function getType(string $type): string
	{
		return match($type) {
			'bool' => 'tinyint',
			'int' => 'int',
			'string' => 'varchar(255)',
			'text' => 'text',
			'datetime' => 'timestamp',
			'float' => 'float',
			'fulltext' => 'text',
		};
	}


	private function getDefault(string $default, string $type): string
	{
		return match($default) {
			'now' => "CURRENT_TIMESTAMP",
			default => match($type) {
				'bool' => $default ? 1 : 0,
				'int' => $default,
				'string' => "'$default'",
				'text' => "'$default'",
			}
		};
	}


	private function reset(): void
	{
		$this->count = 0;
		$this->createTable = [];
		$this->createIndex = [];
		$this->alterTable = [];
	}


	public function setDefaultSchema(string $defaultSchema): self
	{
		$this->defaultSchema = $defaultSchema;
		return $this;
	}
}