<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Entity\Block\BlockType\BlockType;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class BlockTypeTransformer extends TransformerAbstract
{
    /**
     * Get what the API hands to its clients for a block type.
     *
     * @return array<string,mixed>
     */
    public function transform(BlockType $blockType): array
    {
        // the method returns FALSE when the block type belongs to no package
        $packageHandle = (string) $blockType->getPackageHandle();
        $controller = $blockType->getController();

        return [
            'handle' => (string) $blockType->getBlockTypeHandle(),
            'name' => (string) $blockType->getBlockTypeName(),
            'description' => (string) $blockType->getBlockTypeDescription(),
            'package' => $packageHandle === '' ? null : $packageHandle,
            'value_schema' => $controller === null ? null : $controller->getApiHandler()->getApiValueSchema(),
        ];
    }
}
