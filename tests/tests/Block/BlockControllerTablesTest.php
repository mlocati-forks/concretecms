<?php

declare(strict_types=1);

namespace Concrete\Tests\Block;

use Concrete\Core\Block\BlockType\BlockType;
use Concrete\TestHelpers\Database\ConcreteDatabaseTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Block\BlockController::getBlockTables()
 */
class BlockControllerTablesTest extends ConcreteDatabaseTestCase
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

        $tables = $controller->getBlockTables();

        static::assertSame('btContentImage', $tables->getMainTable());
        static::assertSame(['btContentImage', 'btContentImageBreakpoints'], $tables->getTables());
        static::assertNotNull($tables->getColumn('btContentImage', 'cropImage'));
    }

    public function testTheTablesAreReadJustOnce(): void
    {
        $controller = $this->getController('image');

        static::assertSame($controller->getBlockTables(), $controller->getBlockTables());
        static::assertNull($this->getController('horizontal_rule')->getBlockTables());
    }

    public function testABlockTypeThatOwnsNoData(): void
    {
        $controller = $this->getController('horizontal_rule');

        static::assertNull($controller->getBlockTables());
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
                <table name="btBlockTablesExample">
                    <field name="bID" type="integer"><key/><unsigned/></field>
                    <field name="title" type="string" size="128" comment="The title of the block"/>
                </table>
            </schema>
            EOT);
            $controller = new class extends \Concrete\Core\Block\BlockController {
                protected $btHandle = 'declared_tables_example';

                protected $btTable = 'btBlockTablesExample';
            };

            $tables = $controller->getBlockTables();

            static::assertNotNull($tables);
            static::assertSame(['btBlockTablesExample'], $tables->getTables());
            static::assertSame('The title of the block', $tables->getColumn('btBlockTablesExample', 'title')->getComment());
        } finally {
            $filesystem->deleteDirectory($directory);
        }
    }

    public function testABlockTypeUsingATableThatNothingCreates(): void
    {
        $controller = new class extends \Concrete\Core\Block\BlockController {
            protected $btHandle = 'horizontal_rule';

            protected $btTable = 'btSomeTable';
        };

        $this->expectException(\RuntimeException::class);

        $controller->getBlockTables();
    }

    public function testABlockTypeCreatingATableThatItDoesntUse(): void
    {
        $controller = new class extends \Concrete\Core\Block\BlockController {
            protected $btHandle = 'image';
        };

        $this->expectException(\RuntimeException::class);

        $controller->getBlockTables();
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
