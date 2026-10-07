<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Block;

use Concrete\Core\Api\Block\BlockTypeCatalog;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Entity\Block\BlockType\BlockType as BlockTypeEntity;
use Concrete\TestHelpers\Database\ConcreteDatabaseTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @covers \Concrete\Core\Api\Block\BlockTypeCatalog
 */
class BlockTypeCatalogTest extends ConcreteDatabaseTestCase
{
    protected $tables = [
        // the scrapbook block type asks its original block about itself while it is installed
        'Blocks',
        'BlockTypeSets',
    ];

    protected $entityClassNames = [
        BlockTypeEntity::class,
        \Concrete\Core\Entity\Package::class,
    ];

    public function testTheBlockTypesAnEditorPlacesAreDescribed(): void
    {
        $this->installBlockType('image');

        static::assertContains('image', $this->getCatalogedHandles());
        static::assertNotNull((new BlockTypeCatalog())->getByHandle('image'));
    }

    /**
     * @dataProvider provideInternalHandlesThatAreDescribed
     */
    public function testAnInternalBlockTypeFoundAmongTheBlocksOfAnAreaIsDescribed(string $handle): void
    {
        $this->installBlockType($handle);

        static::assertContains($handle, $this->getCatalogedHandles());
        static::assertNotNull((new BlockTypeCatalog())->getByHandle($handle));
    }

    /**
     * @return array<string[]>
     */
    public static function provideInternalHandlesThatAreDescribed(): array
    {
        return [
            [BLOCK_HANDLE_LAYOUT_PROXY],
            [BLOCK_HANDLE_CONTAINER_PROXY],
            [BLOCK_HANDLE_STACK_PROXY],
            [BLOCK_HANDLE_SCRAPBOOK_PROXY],
        ];
    }

    /**
     * @dataProvider provideInternalHandlesThatAreNotDescribed
     */
    public function testAnInternalBlockTypeTheCmsWritesByItselfIsNotDescribed(string $handle): void
    {
        $blockType = $this->installBlockType($handle);

        static::assertTrue($blockType->isBlockTypeInternal());
        static::assertNotContains($handle, $this->getCatalogedHandles());
        static::assertNull((new BlockTypeCatalog())->getByHandle($handle));
    }

    /**
     * @return array<string[]>
     */
    public static function provideInternalHandlesThatAreNotDescribed(): array
    {
        return [
            [BLOCK_HANDLE_BOARD_SLOT_PROXY],
            [BLOCK_HANDLE_PAGE_TYPE_OUTPUT_PROXY],
            ['core_theme_documentation_toc'],
        ];
    }

    public function testNoBlockTypeIsFoundByAHandleThisInstallationHasNot(): void
    {
        static::assertNull((new BlockTypeCatalog())->getByHandle('whatever_block_type'));
    }

    /**
     * @return string[]
     */
    private function getCatalogedHandles(): array
    {
        $handles = [];
        foreach ((new BlockTypeCatalog())->getList() as $blockType) {
            $handles[] = (string) $blockType->getBlockTypeHandle();
        }

        return $handles;
    }

    private function installBlockType(string $handle): BlockTypeEntity
    {
        $blockType = BlockType::getByHandle($handle);
        if ($blockType === null) {
            BlockType::installBlockType($handle);
            $blockType = BlockType::getByHandle($handle);
        }

        return $blockType;
    }
}
