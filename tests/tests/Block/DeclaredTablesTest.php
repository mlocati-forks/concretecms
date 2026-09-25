<?php

declare(strict_types=1);

namespace Concrete\Tests\Block;

use Concrete\Core\Block\DeclaredTables;
use Concrete\TestHelpers\Database\ConcreteDatabaseTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

class DeclaredTablesTest extends ConcreteDatabaseTestCase
{
    public function testTheMainTableComesFirst(): void
    {
        $tables = $this->readBlockType('image', 'btContentImage');

        static::assertSame('btContentImage', $tables->getMainTable());
        static::assertSame(['btContentImage', 'btContentImageBreakpoints'], $tables->getTables());
        static::assertSame(['btContentImageBreakpoints'], $tables->getAdditionalTables());
    }

    public function testTheColumnsAreRead(): void
    {
        $tables = $this->readBlockType('image', 'btContentImage');

        $column = $tables->getColumn('btContentImage', 'altText');
        static::assertNotNull($column);
        static::assertSame('string', $column->getType()->getName());
        static::assertSame(255, $column->getLength());
        static::assertFalse($column->getNotnull());
        static::assertNull($column->getDefault());
        static::assertNotSame('', $column->getComment());
    }

    public function testAColumnThatAcceptsNoNull(): void
    {
        $column = $this->readBlockType('image', 'btContentImage')->getColumn('btContentImage', 'openLinkInNewWindow');

        static::assertNotNull($column);
        static::assertSame('boolean', $column->getType()->getName());
        static::assertTrue($column->getNotnull());
        static::assertSame('0', $column->getDefault());
    }

    public function testAColumnFilledByTheDatabase(): void
    {
        $column = $this->readBlockType('image', 'btContentImage')->getColumn('btContentImageBreakpoints', 'id');

        static::assertNotNull($column);
        static::assertTrue($column->getAutoincrement());
        static::assertTrue($column->getUnsigned());
    }

    public function testTablesAreLookedUpCaseInsensitively(): void
    {
        $tables = $this->readBlockType('image', 'btcontentimage');

        static::assertNotSame([], $tables->getColumns('BTCONTENTIMAGE'));
        static::assertNotNull($tables->getColumn('BTCONTENTIMAGE', 'FID'));
        static::assertSame([], $tables->getColumns('btSomethingElse'));
        static::assertNull($tables->getColumn('btContentImage', 'somethingElse'));
    }

    /**
     * Block types may declare their tables in the format that came before doctrine-xml.
     */
    public function testTheOldFormatIsReadToo(): void
    {
        $tables = DeclaredTables::fromFile(DIR_TESTS . '/assets/Block/legacy-db.xml', 'btLegacyExample');

        static::assertSame(['btLegacyExample'], $tables->getTables());
        $column = $tables->getColumn('btLegacyExample', 'isEnabled');
        static::assertNotNull($column);
        static::assertSame('boolean', $column->getType()->getName());
        static::assertTrue($column->getNotnull());
        static::assertSame(255, $tables->getColumn('btLegacyExample', 'title')->getLength());
    }

    public function testTheMainTableIsRequired(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new DeclaredTables(new \Doctrine\DBAL\Schema\Schema(), '');
    }

    public function testTheMainTableMustBeDeclared(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->readBlockType('image', 'btSomeOtherTable');
    }

    public function testAFileThatIsNotADoctrineXml(): void
    {
        $this->expectException(\RuntimeException::class);

        DeclaredTables::fromFile(DIR_BASE_CORE . '/' . DIRNAME_BLOCKS . '/image/view.php', 'btContentImage');
    }

    public function testAFileThatIsNotThere(): void
    {
        $this->expectException(\RuntimeException::class);

        DeclaredTables::fromFile(DIR_BASE_CORE . '/' . DIRNAME_BLOCKS . '/image/there-is-no-such-file.xml', 'btContentImage');
    }

    private function readBlockType(string $handle, string $mainTable): DeclaredTables
    {
        return DeclaredTables::fromFile(
            DIR_BASE_CORE . '/' . DIRNAME_BLOCKS . '/' . $handle . '/' . FILENAME_BLOCK_DB,
            $mainTable
        );
    }
}
