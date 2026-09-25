<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Resources;
use Concrete\Core\Block\Block;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class BaseBlockTransformer extends TransformerAbstract
{
    /**
     * @var string[]
     */
    protected $availableIncludes = [
        'page',
    ];

    /**
     * @return array<string,mixed>
     */
    public function transform(Block $block)
    {
        return [
            'id' => $block->getBlockID(),
            'type' => $block->getBlockTypeHandle(),
            'value' => (object) $block->getController()->getApiHandler()->getApiValue($block),
        ];
    }

    /**
     * @return \League\Fractal\Resource\Item
     */
    public function includePage(Block $block)
    {
        $page = $block->getBlockCollectionObject();

        return new Item($page, new PageTransformer(), Resources::RESOURCE_PAGES);
    }
}
