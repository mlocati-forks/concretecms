<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Resources;
use Concrete\Core\Api\TokenScopes;
use Concrete\Core\Area\Area;
use Concrete\Core\Block\Block;
use Concrete\Core\Permission\Checker;
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
        if (!$this->isVisible($block)) {
            // the ID of every block of an area travels, since it takes them all to place one among them
            return ['id' => $block->getBlockID()];
        }

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

    /**
     * Whether the request may see a block: the scope of the token settles it first, the way it does
     * for everything else of this installation travelling inside something else, and then the
     * permissions of the area holding the block and of the block itself.
     */
    protected function isVisible(Block $block): bool
    {
        if (!app(TokenScopes::class)->has('blocks:read')) {
            return false;
        }
        $area = $block->getBlockAreaObject();
        if ($area instanceof Area && !(new Checker($area))->canViewArea()) {
            return false;
        }

        return (bool) (new Checker($block))->canViewBlock();
    }
}
