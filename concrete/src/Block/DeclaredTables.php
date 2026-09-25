<?php

declare(strict_types=1);

namespace Concrete\Core\Block;

use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Database\Schema\Schema as SchemaParser;
use Concrete\Core\Filesystem\FileLocator;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Schema;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The database tables of a block type, as its db.xml file declares them: reading them here means
 * reading what the block type says about itself, and not what the database ended up with.
 *
 * @readonly
 *
 * @see \Concrete\Core\Block\BlockController::getDeclaredTables()
 */
final class DeclaredTables
{
    /**
     * @var string
     */
    private $mainTable;

    /**
     * @var \Doctrine\DBAL\Schema\Schema
     */
    private $schema;

    /**
     * The names of the declared tables, the main one being the first.
     *
     * @var string[]
     */
    private $tables;

    /**
     * @param string $mainTable the table holding one row per block, that is the btTable of the block type
     *
     * @throws \InvalidArgumentException when the schema doesn't declare the main table
     */
    public function __construct(Schema $schema, string $mainTable)
    {
        if ($mainTable === '' || !$schema->hasTable($mainTable)) {
            throw new \InvalidArgumentException(t('The %s table is not declared', $mainTable === '' ? '?' : $mainTable));
        }
        $this->schema = $schema;
        $this->mainTable = $mainTable;
        $this->tables = [];
        foreach ($schema->getTables() as $table) {
            $this->tables[] = $table->getName();
        }
        usort($this->tables, function (string $a, string $b): int {
            return $this->isMainTable($b) <=> $this->isMainTable($a);
        });
    }

    /**
     * Read the tables declared by the db.xml file of the block type of a controller.
     *
     * @return static|null NULL when the block type owns no data at all
     *
     * @throws \RuntimeException when the block type declares a table without the file that creates it, or the other way around
     */
    public static function forBlockController(BlockController $controller): ?self
    {
        $handle = $controller->getBlockTypeHandle();
        $mainTable = (string) $controller->getBlockTypeDatabaseTable();
        $file = $handle === '' ? '' : self::locateFile($handle, self::getPackageHandle($controller));
        if ($mainTable === '') {
            if ($file !== '') {
                throw new \RuntimeException(t('The block type %1$s comes with a %2$s file, but it declares no database table', $handle, FILENAME_BLOCK_DB));
            }

            return null;
        }
        if ($file === '') {
            throw new \RuntimeException(t('The block type %1$s uses the %2$s database table, but it comes with no %3$s file', $handle, $mainTable, FILENAME_BLOCK_DB));
        }

        return self::fromFile($file, $mainTable);
    }

    /**
     * Read the tables declared by a db.xml file, in any of the formats that the installer accepts.
     *
     * @param string $file the full path of the db.xml file
     * @param string $mainTable the table holding one row per block, that is the btTable of the block type
     *
     * @throws \RuntimeException when the file can't be read
     * @throws \Exception when the file isn't in a format that the installer accepts
     */
    public static function fromFile(string $file, string $mainTable, ?Connection $connection = null): self
    {
        // a file that isn't XML makes libxml complain to whoever is listening: we answer for it ourselves
        $internalErrors = libxml_use_internal_errors(true);
        try {
            $xml = is_file($file) ? simplexml_load_file($file) : false;
        } finally {
            libxml_use_internal_errors($internalErrors);
            libxml_clear_errors();
        }
        if (!$xml instanceof \SimpleXMLElement) {
            throw new \RuntimeException(t('Failed to read the file %s', $file));
        }
        $parser = SchemaParser::getSchemaParser($xml);
        // we want every table the file declares, not just the ones the database is still missing
        $parser->setIgnoreExistingTables(false);
        $connection = $connection ?? app(Connection::class);

        return new self($parser->parse($connection), $mainTable);
    }

    /**
     * Get the name of the table holding one row per block, that is the btTable of the block type.
     */
    public function getMainTable(): string
    {
        return $this->mainTable;
    }

    /**
     * Get the names of the declared tables, the main one being the first.
     *
     * @return string[]
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * Get the names of the declared tables, excluding the main one.
     *
     * @return string[]
     */
    public function getAdditionalTables(): array
    {
        return array_values(array_filter($this->tables, function (string $table): bool {
            return !$this->isMainTable($table);
        }));
    }

    /**
     * Get the columns of a table, in the order they are declared.
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getColumns(string $table): array
    {
        return $this->schema->hasTable($table) ? array_values($this->schema->getTable($table)->getColumns()) : [];
    }

    /**
     * Get a column of a table (NULL when the table doesn't declare it).
     */
    public function getColumn(string $table, string $column): ?Column
    {
        if (!$this->schema->hasTable($table)) {
            return null;
        }
        $declared = $this->schema->getTable($table);

        return $declared->hasColumn($column) ? $declared->getColumn($column) : null;
    }

    /**
     * Get the handle of the package that the block type of a controller belongs to (an empty string
     * when it belongs to the core, or when the application overrides it).
     */
    private static function getPackageHandle(BlockController $controller): string
    {
        // the controller of the block type of a package lives in the namespace of the package
        $parts = explode('\\', get_class($controller));
        if (count($parts) < 5 || $parts[0] !== 'Concrete' || $parts[1] !== 'Package' || $parts[3] !== 'Block') {
            return '';
        }

        return (string) app('helper/text')->uncamelcase($parts[2]);
    }

    /**
     * Get the full path of the db.xml file of a block type (an empty string when it has none).
     */
    private static function locateFile(string $blockTypeHandle, string $packageHandle): string
    {
        $locator = app(FileLocator::class);
        if ($packageHandle !== '') {
            $locator->addLocation(new FileLocator\PackageLocation($packageHandle));
        }
        $record = $locator->getRecord(DIRNAME_BLOCKS . '/' . $blockTypeHandle . '/' . FILENAME_BLOCK_DB);

        return $record->exists() ? $record->getFile() : '';
    }

    private function isMainTable(string $table): bool
    {
        return strcasecmp($table, $this->mainTable) === 0;
    }
}
