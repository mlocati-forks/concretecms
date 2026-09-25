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
     * Get what the API hands to its clients for a block.
     *
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
     * Get the page holding a block, which the clients of the API may ask for along with it.
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includePage(Block $block)
    {
        $page = $block->getBlockCollectionObject();

        return new Item($page, new PageTransformer(), Resources::RESOURCE_PAGES);
    }
}
