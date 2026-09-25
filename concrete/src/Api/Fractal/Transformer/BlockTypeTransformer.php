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
        $controller = $blockType->getController();
        $handler = $controller === null ? null : $controller->getApiHandler();
        $description = $handler === null ? '' : $handler->getCustomApiDescription();
        if ($description === '') {
            $description = (string) $blockType->getBlockTypeDescription();
        }

        return [
            'handle' => (string) $blockType->getBlockTypeHandle(),
            'name' => (string) $blockType->getBlockTypeName(),
            'description' => $description,
            'package' => (string) $blockType->getPackageHandle(),
            'value_schema' => $handler === null ? null : $handler->getApiValueSchema(),
        ];
    }
}
