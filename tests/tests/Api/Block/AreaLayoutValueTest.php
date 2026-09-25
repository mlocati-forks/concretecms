<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Block;

use Concrete\Core\Area\Area;
use Concrete\Core\Area\SubArea;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Page\Page;
use Concrete\TestHelpers\Block\BlockApiTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests the layouts that the API adds to a page, whose columns have to be ready to receive blocks.
 *
 * @see \Concrete\Block\CoreAreaLayout\Api
 */
class AreaLayoutValueTest extends BlockApiTestCase
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Block\BlockApiTestCase::getTables()
     */
    protected function getTables()
    {
        return array_merge(parent::getTables(), [
            'AreaLayoutColumns',
            'AreaLayoutCustomColumns',
            'AreaLayoutPresets',
            'AreaLayouts',
            'AreaLayoutThemeGridColumns',
            'AreaLayoutsUsingPresets',
        ]);
    }

    public function testALayoutIsAddedWithTheColumnsItIsGiven(): void
    {
        $block = $this->addBlockFromApiValue('core_area_layout', $this->getThemeGridValue());

        $value = $this->getApiValue($block);

        static::assertSame('theme-grid', $value['type']);
        static::assertSame(12, $value['maxColumns']);
        static::assertCount(2, $value['columns']);
        static::assertSame(8, $value['columns'][0]['span']);
        static::assertSame(4, $value['columns'][1]['span']);
        static::assertSame(1, $value['columns'][1]['offset']);
    }

    public function testEveryColumnComesWithAnAreaOfItsOwn(): void
    {
        $block = $this->addBlockFromApiValue('core_area_layout', $this->getThemeGridValue());
        $page = $this->getPage($block);

        foreach ($this->getApiValue($block)['columns'] as $column) {
            static::assertNotSame('', $column['area']);
            $area = Area::get($page, $column['area']);
            // an area with no parent is one that the API created by itself, unrelated to the column
            static::assertInstanceOf(SubArea::class, $area, "The area {$column['area']} isn't the one of the column");
            static::assertSame((int) $block->getBlockAreaObject()->getAreaID(), (int) $area->getAreaParentID());
        }
    }

    public function testTheBlocksAddedToAColumnAreThere(): void
    {
        $block = $this->addBlockFromApiValue('core_area_layout', $this->getThemeGridValue());
        $page = $this->getPage($block);
        $areaHandle = $this->getApiValue($block)['columns'][0]['area'];

        if (BlockType::getByHandle('content') === null) {
            BlockType::installBlockType('content');
        }
        $page->addBlock(BlockType::getByHandle('content'), Area::getOrCreate($page, $areaHandle), ['content' => 'In the first column']);

        $blocks = $this->getPage($block)->getBlocks($areaHandle);
        static::assertCount(1, $blocks);
    }

    public function testTheSizesOfTheColumnsAreUpdated(): void
    {
        $block = $this->addBlockFromApiValue('core_area_layout', $this->getThemeGridValue());
        $value = $this->getApiValue($block);
        $value['columns'][0]['span'] = 6;
        $value['columns'][1]['span'] = 6;
        $value['columns'][1]['offset'] = 0;

        $this->updateBlock($block, $value);

        $written = $this->getApiValue($this->getBlock($this->getPage($block)));
        static::assertSame(6, $written['columns'][0]['span']);
        static::assertSame(6, $written['columns'][1]['span']);
        static::assertSame(0, $written['columns'][1]['offset']);
        // the areas of the columns, and so the blocks placed in them, are the ones of before
        static::assertSame(array_column($value['columns'], 'area'), array_column($written['columns'], 'area'));
    }

    /**
     * Get a value describing a layout of two columns of the grid of the theme.
     *
     * @return array<string,mixed>
     */
    private function getThemeGridValue(): array
    {
        return [
            'type' => 'theme-grid',
            'maxColumns' => 12,
            'columns' => [
                ['span' => 8, 'offset' => 0],
                ['span' => 4, 'offset' => 1],
            ],
        ];
    }

    private function getPage(\Concrete\Core\Block\Block $block): Page
    {
        return Page::getByID($block->getBlockCollectionObject()->getCollectionID(), 'RECENT');
    }
}
