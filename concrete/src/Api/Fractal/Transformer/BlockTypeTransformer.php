<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\BlockType as BlockTypeModel;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class BlockTypeTransformer extends TransformerAbstract
{
    /**
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

        $model = new BlockTypeModel();
        $model->handle = (string) $blockType->getBlockTypeHandle();
        $model->name = (string) $blockType->getBlockTypeName();
        $model->description = $description;
        $model->package = (string) $blockType->getPackageHandle();
        $model->value_schema = $handler === null ? null : $handler->getApiValueSchema();

        return $model->jsonSerialize();
    }
}
