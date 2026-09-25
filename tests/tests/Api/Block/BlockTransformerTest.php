<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Block;

use Concrete\Core\Api\Fractal\Transformer\BaseBlockTransformer;
use Concrete\Core\Block\Block;
use Concrete\TestHelpers\Block\BlockApiTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests what the API hands to its clients for a block.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\BaseBlockTransformer
 */
class BlockTransformerTest extends BlockApiTestCase
{
    public function testTheValueIsTheOneOfTheHandler(): void
    {
        $block = $this->addBlock('image', ['fID' => 0, 'altText' => 'A tiny image', 'sizingOption' => 'full_size']);

        $transformed = $this->transform($block);

        static::assertSame($block->getBlockID(), $transformed['id']);
        static::assertSame('image', $transformed['type']);
        static::assertSame($this->getApiValue($block), (array) $transformed['value']);
        static::assertSame('A tiny image', $transformed['value']->altText);
    }

    /**
     * A value is an object, so that a client always finds one where the schema says there is one.
     */
    public function testTheValueOfABlockTypeThatKeepsNothingIsAnObject(): void
    {
        $block = $this->addBlock('horizontal_rule', []);

        $value = $this->transform($block)['value'];

        static::assertEquals(new \stdClass(), $value);
        static::assertSame('{}', json_encode($value));
    }

    /**
     * @return array<string,mixed>
     */
    private function transform(Block $block): array
    {
        return (new BaseBlockTransformer())->transform($block);
    }
}
