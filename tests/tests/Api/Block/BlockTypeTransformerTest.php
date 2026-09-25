<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Block;

use Concrete\Core\Api\Fractal\Transformer\BlockTypeTransformer;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Entity\Block\BlockType\BlockType as BlockTypeEntity;
use Concrete\TestHelpers\Database\ConcreteDatabaseTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests what the block types endpoints hand to their clients.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\BlockTypeTransformer
 */
class BlockTypeTransformerTest extends ConcreteDatabaseTestCase
{
    protected $tables = [
        'BlockTypeSets',
    ];

    protected $entityClassNames = [
        BlockTypeEntity::class,
        \Concrete\Core\Entity\Package::class,
    ];

    public function testABlockTypeIsDescribedWithTheSchemaOfItsValue(): void
    {
        $transformed = $this->transform('image');

        static::assertSame('image', $transformed['handle']);
        static::assertSame('Image', $transformed['name']);
        // the block types of the core belong to no package
        static::assertNull($transformed['package']);
        static::assertSame('object', $transformed['value_schema']['type']);
        static::assertArrayHasKey('altText', $transformed['value_schema']['properties']);
    }

    public function testTheSchemaIsTheOneOfTheHandlerOfTheBlockType(): void
    {
        $blockType = $this->getBlockType('image');

        $transformed = (new BlockTypeTransformer())->transform($blockType);

        static::assertSame($blockType->getController()->getApiHandler()->getApiValueSchema(), $transformed['value_schema']);
    }

    /**
     * @return array<string,mixed>
     */
    private function transform(string $handle): array
    {
        return (new BlockTypeTransformer())->transform($this->getBlockType($handle));
    }

    private function getBlockType(string $handle): BlockTypeEntity
    {
        $blockType = BlockType::getByHandle($handle);
        if ($blockType === null) {
            BlockType::installBlockType($handle);
            $blockType = BlockType::getByHandle($handle);
        }

        return $blockType;
    }
}
