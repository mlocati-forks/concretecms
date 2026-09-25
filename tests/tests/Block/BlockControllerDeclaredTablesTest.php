<?php

declare(strict_types=1);

namespace Concrete\Tests\Block;

use Concrete\Core\Block\BlockType\BlockType;
use Concrete\TestHelpers\Database\ConcreteDatabaseTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests that a block type controller finds the db.xml file of its own block type.
 *
 * @see \Concrete\Core\Block\BlockController::getDeclaredTables()
 */
class BlockControllerDeclaredTablesTest extends ConcreteDatabaseTestCase
{
    protected $tables = [
        'BlockTypeSets',
    ];

    protected $entityClassNames = [
        \Concrete\Core\Entity\Block\BlockType\BlockType::class,
        \Concrete\Core\Entity\Package::class,
    ];

    public function testTheTablesOfABlockTypeAreRead(): void
    {
        $controller = $this->getController('image');

        $tables = $controller->getDeclaredTables();

        static::assertSame('btContentImage', $tables->getMainTable());
        static::assertSame(['btContentImage', 'btContentImageBreakpoints'], $tables->getTables());
        static::assertNotNull($tables->getColumn('btContentImage', 'cropImage'));
    }

    public function testTheTablesAreReadJustOnce(): void
    {
        $controller = $this->getController('image');

        static::assertSame($controller->getDeclaredTables(), $controller->getDeclaredTables());
        static::assertNull($this->getController('horizontal_rule')->getDeclaredTables());
    }

    public function testABlockTypeThatOwnsNoData(): void
    {
        $controller = $this->getController('horizontal_rule');

        static::assertNull($controller->getDeclaredTables());
    }

    /**
     * Block types may live in the application directory, outside the core and outside a package.
     */
    public function testABlockTypeOfTheApplication(): void
    {
        $directory = DIR_APPLICATION . '/' . DIRNAME_BLOCKS . '/declared_tables_example';
        $filesystem = new \Illuminate\Filesystem\Filesystem();
        $filesystem->makeDirectory($directory, 0777, true);
        try {
            $filesystem->put($directory . '/' . FILENAME_BLOCK_DB, <<<'EOT'
                <?xml version="1.0" encoding="UTF-8"?>
                <schema xmlns="http://www.concrete5.org/doctrine-xml/0.5">
                    <table name="btDeclaredTablesExample">
                        <field name="bID" type="integer"><key/><unsigned/></field>
                        <field name="title" type="string" size="128" comment="The title of the block"/>
                    </table>
                </schema>
                EOT);
            $controller = new class() extends \Concrete\Core\Block\BlockController {
                protected $btHandle = 'declared_tables_example';

                protected $btTable = 'btDeclaredTablesExample';
            };

            $tables = $controller->getDeclaredTables();

            static::assertNotNull($tables);
            static::assertSame(['btDeclaredTablesExample'], $tables->getTables());
            static::assertSame('The title of the block', $tables->getColumn('btDeclaredTablesExample', 'title')->getComment());
        } finally {
            $filesystem->deleteDirectory($directory);
        }
    }

    public function testABlockTypeUsingATableThatNothingCreates(): void
    {
        $controller = new class() extends \Concrete\Core\Block\BlockController {
            protected $btHandle = 'horizontal_rule';

            protected $btTable = 'btSomeTable';
        };

        $this->expectException(\RuntimeException::class);

        $controller->getDeclaredTables();
    }

    public function testABlockTypeCreatingATableThatItDoesntUse(): void
    {
        $controller = new class() extends \Concrete\Core\Block\BlockController {
            protected $btHandle = 'image';
        };

        $this->expectException(\RuntimeException::class);

        $controller->getDeclaredTables();
    }

    private function getController(string $handle): \Concrete\Core\Block\BlockController
    {
        $blockType = BlockType::getByHandle($handle);
        if ($blockType === null) {
            BlockType::installBlockType($handle);
            $blockType = BlockType::getByHandle($handle);
        }

        return $blockType->getController();
    }
}
