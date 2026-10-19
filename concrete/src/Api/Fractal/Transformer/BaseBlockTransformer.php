<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Resources;
use Concrete\Core\Api\Model\CustomStyle as CustomStyleModel;
use Concrete\Core\Api\Style\CustomStyleDescriber;
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
     * @var \Concrete\Core\Api\Style\CustomStyleDescriber|null
     */
    private $customStyleDescriber;

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
            'custom_template' => (string) $block->getBlockFilename(),
            'custom_style' => $this->describeCustomStyle($block),
            'value' => (object) $block->getController()->getApiHandler()->getApiValue($block),
        ];
    }

    /**
     * Describe the custom style of a block, NULL where it has none of its own.
     */
    private function describeCustomStyle(Block $block): ?CustomStyleModel
    {
        $style = $block->getCustomStyle();

        return $style === null ? null : $this->getCustomStyleDescriber()->describe($style->getStyleSet());
    }

    /**
     * Get the describer of the custom styles: one instance for all the blocks of an answer.
     */
    protected function getCustomStyleDescriber(): CustomStyleDescriber
    {
        if ($this->customStyleDescriber === null) {
            $this->customStyleDescriber = app(CustomStyleDescriber::class);
        }

        return $this->customStyleDescriber;
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
