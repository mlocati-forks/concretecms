<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Block;

use Concrete\TestHelpers\Block\BlockApiTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests the value that the API reads and writes for a block type that keeps its settings in its own
 * table, the image block standing for all of them.
 *
 * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler
 */
class ImageValueTest extends BlockApiTestCase
{
    public function testEveryColumnHasTheTypeItDeclares(): void
    {
        $block = $this->addImage();

        $value = $this->getApiValue($block);

        static::assertSame('A tiny image', $value['altText']);
        static::assertSame(400, $value['maxWidth']);
        static::assertTrue($value['cropImage']);
        static::assertFalse($value['openLinkInNewWindow']);
        static::assertSame('constrain_size', $value['sizingOption']);
        // the blocks share it, so it isn't part of their value
        static::assertArrayNotHasKey('bID', $value);
    }

    public function testTheValueSurvivesARoundTrip(): void
    {
        $block = $this->addImage();
        $value = $this->getApiValue($block);

        $this->updateBlock($block, $value);

        static::assertSame($value, $this->getApiValue($this->getBlock($block->getBlockCollectionObject())));
    }

    public function testTheValueChangesWhenTheApiWritesIt(): void
    {
        $block = $this->addImage();

        $this->updateBlock($block, ['altText' => 'Another text', 'cropImage' => false]);

        $value = $this->getApiValue($this->getBlock($block->getBlockCollectionObject()));
        static::assertSame('Another text', $value['altText']);
        static::assertFalse($value['cropImage']);
    }

    public function testWhatTheApiDoesntMentionIsKept(): void
    {
        $block = $this->addImage();

        $this->updateBlock($block, ['altText' => 'Another text']);

        $value = $this->getApiValue($this->getBlock($block->getBlockCollectionObject()));
        static::assertSame(400, $value['maxWidth']);
        static::assertTrue($value['cropImage']);
        static::assertSame('constrain_size', $value['sizingOption']);
    }

    private function addImage(): \Concrete\Core\Block\Block
    {
        return $this->addBlock('image', [
            'fID' => 0,
            'altText' => 'A tiny image',
            'sizingOption' => 'constrain_size',
            'maxWidth' => 400,
            'maxHeight' => 300,
            'cropImage' => 1,
            'openLinkInNewWindow' => 0,
        ]);
    }
}
