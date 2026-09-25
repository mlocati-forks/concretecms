<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Block;

use Concrete\Core\Api\FileIdentifier;
use Concrete\Core\Block\Block;
use Concrete\Core\Block\ReferenceColumns;
use Concrete\Core\Database\Connection\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Types;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The value of a block type that keeps its settings in the columns of its own table: it's the row
 * of that table, described by the db.xml file that declares it.
 *
 * A block type keeping something in another table, or in more than one row, comes with an Api class
 * of its own: this one has no way to guess what that something means.
 *
 * @see \Concrete\Core\Api\Block\BlockApiHandler
 */
class DefaultBlockApiHandler extends BlockApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $properties = [];
        foreach ($this->getColumns() as $column) {
            $properties[$column->getName()] = $this->describeColumn($column);
        }

        return [
            'type' => 'object',
            'properties' => $properties === [] ? (object) [] : $properties,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getApiValue()
     */
    public function getApiValue(Block $block): array
    {
        $row = $this->getMainTableRow($block);
        $value = [];
        foreach ($this->getColumns() as $column) {
            $name = $column->getName();
            $value[$name] = $this->formatDatabaseValueForApi($row[$name] ?? null, $column);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getSaveArgumentsFromApiValue()
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        // the save() method resets the settings it isn't given: let's keep the current ones
        $current = $block === null ? [] : $this->getApiValue($block);
        $arguments = [];
        foreach ($this->getColumns() as $column) {
            $name = $column->getName();
            if (array_key_exists($name, $value)) {
                $arguments[$name] = $this->formatApiValueForSave($value[$name], $column);
            } elseif (array_key_exists($name, $current)) {
                $arguments[$name] = $this->formatApiValueForSave($current[$name], $column);
            }
        }

        return $arguments;
    }

    /**
     * Get the columns that a table contributes to the value, that is the ones the blocks don't
     * share and that the database doesn't fill by itself.
     *
     * @param string $table the table the columns belong to (empty: the main table of the block type)
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    protected function getColumns(string $table = ''): array
    {
        $tables = $this->controller->getDeclaredTables();
        if ($tables === null) {
            return [];
        }

        return array_values(array_filter(
            $tables->getColumns($table === '' ? $tables->getMainTable() : $table),
            static function (Column $column): bool {
                return strcasecmp($column->getName(), 'bID') !== 0 && !$column->getAutoincrement();
            }
        ));
    }

    /**
     * Describe a column of the value.
     *
     * @return array<string,mixed>
     */
    protected function describeColumn(Column $column): array
    {
        $type = $this->getJsonType($column);
        $reference = $this->controller->getReferenceColumns()->getReference($column->getName());
        // the API tells a file apart by its UUID, which is a string, when the file has one
        $types = $reference === ReferenceColumns::FILE ? ['string', 'integer'] : [$type];
        if (!$column->getNotnull()) {
            $types[] = 'null';
        }
        $schema = ['type' => count($types) === 1 ? $types[0] : $types];
        // the comment of the column says what the field holds, and we add how it must be written
        $description = array_filter([(string) $column->getComment(), $this->describeReference($reference)]);
        if ($description !== []) {
            $schema['description'] = implode("\n", $description);
        }
        if ($type === 'string' && ($length = $column->getLength()) !== null) {
            $schema['maxLength'] = $length;
        }
        if ($type === 'integer' && $column->getUnsigned()) {
            $schema['minimum'] = 0;
        }
        if (($default = $column->getDefault()) !== null) {
            $schema['default'] = $this->formatDatabaseValueForApi($default, $column);
        }
        if ($reference !== '') {
            $schema['x-concrete-reference'] = $reference;
        }

        return $schema;
    }

    /**
     * Get the JSON type of a column.
     */
    protected function getJsonType(Column $column): string
    {
        switch ($column->getType()->getName()) {
            case Types::BOOLEAN:
                return 'boolean';
            case Types::INTEGER:
            case Types::SMALLINT:
            case Types::BIGINT:
                return 'integer';
            case Types::FLOAT:
            case Types::DECIMAL:
                return 'number';
            default:
                return 'string';
        }
    }

    /**
     * Turn a value of the database into the value that the API hands to its clients.
     *
     * @return bool|int|float|string|null
     */
    protected function formatDatabaseValueForApi($value, Column $column)
    {
        if ($value === null) {
            return;
        }
        switch ($this->controller->getReferenceColumns()->getReference($column->getName())) {
            case ReferenceColumns::FILE:
                return (int) $value === 0 ? 0 : app(FileIdentifier::class)->forApi($value);
            case ReferenceColumns::CONTENT:
                return app(FileIdentifier::class)->contentForApi((string) $value);
        }
        switch ($this->getJsonType($column)) {
            case 'boolean':
                return (bool) (int) $value;
            case 'integer':
                return (int) $value;
            case 'number':
                return (float) $value;
            default:
                return (string) $value;
        }
    }

    /**
     * Turn a value received by the API into what the save() method of the controller wants, which is
     * what its own form would send.
     *
     * @return int|float|string|null
     */
    protected function formatApiValueForSave($value, Column $column)
    {
        if ($value === null) {
            return;
        }
        if ($this->controller->getReferenceColumns()->getReference($column->getName()) === ReferenceColumns::FILE) {
            return $value === 0 || $value === '0' ? 0 : app(FileIdentifier::class)->fromApi($value);
        }
        switch ($this->getJsonType($column)) {
            case 'boolean':
                // a block type stores its flags as 0 and 1, and reads them back as strings
                return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            case 'integer':
                return (int) $value;
            case 'number':
                return (float) $value;
            default:
                return (string) $value;
        }
    }

    /**
     * Get the JSON Schema of the rows that a block owns in one of the other tables of its block type.
     *
     * @return array<string,mixed>
     */
    protected function describeTableRows(string $table, string $description): array
    {
        $properties = [];
        foreach ($this->getColumns($table) as $column) {
            $properties[$column->getName()] = $this->describeColumn($column);
        }

        return [
            'type' => 'array',
            'description' => $description,
            'items' => [
                'type' => 'object',
                'properties' => $properties === [] ? (object) [] : $properties,
            ],
        ];
    }

    /**
     * Get the rows that a block owns in one of the other tables of its block type, as the API hands
     * them to its clients.
     *
     * @param string $orderBy the column the rows are sorted by (empty: they come as they are)
     *
     * @return array<int,array<string,mixed>>
     */
    protected function getTableRows(Block $block, string $table, string $orderBy = ''): array
    {
        $columns = $this->getColumns($table);
        if ($columns === []) {
            return [];
        }
        $connection = app(Connection::class);
        $platform = $connection->getDatabasePlatform();
        $sql = 'SELECT * FROM ' . $platform->quoteSingleIdentifier($table) . ' WHERE bID = ?';
        if ($orderBy !== '') {
            $sql .= ' ORDER BY ' . $platform->quoteSingleIdentifier($orderBy);
        }
        $rows = [];
        foreach ($connection->fetchAllAssociative($sql, [$block->getBlockID()]) as $row) {
            $value = [];
            foreach ($columns as $column) {
                $name = $column->getName();
                $value[$name] = $this->formatDatabaseValueForApi($row[$name] ?? null, $column);
            }
            $rows[] = $value;
        }

        return $rows;
    }

    /**
     * Get the row of the main table holding the settings of a block.
     *
     * @return array<string,mixed>
     */
    protected function getMainTableRow(Block $block): array
    {
        $tables = $this->controller->getDeclaredTables();
        if ($tables === null) {
            return [];
        }
        $connection = app(Connection::class);
        $table = $connection->getDatabasePlatform()->quoteSingleIdentifier($tables->getMainTable());
        $row = $connection->fetchAssociative("SELECT * FROM {$table} WHERE bID = ? LIMIT 1", [$block->getBlockID()]);

        return $row === false ? [] : $row;
    }
}
