<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Page\Page;
use Concrete\Core\Api\Model\CustomStyle as CustomStyleModel;
use Concrete\Core\Api\Style\CustomStyleDescriber;
use Concrete\Core\Area\Area;
use Concrete\Core\Area\ApiArea;
use Concrete\Core\Api\Fractal\Transformer\Traits\GetPageApiAreasTrait;
use Concrete\Core\Api\Resources;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\TransformerAbstract;

class AreaTransformer extends TransformerAbstract
{

    use GetPageApiAreasTrait;

    /**
     * @var \Concrete\Core\Api\Style\CustomStyleDescriber|null
     */
    private $customStyleDescriber;

    protected $defaultIncludes = [
        'blocks',
    ];

    protected $availableIncludes = [
        'content',
    ];

    public function transform(ApiArea $area)
    {
        $data = [];
        $data['name'] = $area->getAreaHandle();
        $data['custom_style'] = $this->describeCustomStyle($area);
        return $data;
    }

    public function includeBlocks(ApiArea $area)
    {
        $blocks = $area->getPage()->getBlocks($area->getAreaHandle());
        return new Collection($blocks, new BaseBlockTransformer(), Resources::RESOURCE_BLOCKS);
    }

    /**
     * Describe the custom style of an area, NULL where it has none of its own. The area of the page is the one
     * to ask about, global areas included: the core goes looking in their stack by itself.
     */
    private function describeCustomStyle(ApiArea $area): ?CustomStyleModel
    {
        $page = $area->getPage();
        if (!$page->getCollectionID()) {
            return null;
        }
        $pageArea = Area::get($page, $area->getAreaHandle());
        $style = $pageArea instanceof Area ? $page->getAreaCustomStyle($pageArea) : null;

        return $style === null ? null : $this->getCustomStyleDescriber()->describe($style->getStyleSet());
    }

    /**
     * Get the describer of the custom styles: one instance for all the areas of an answer.
     */
    protected function getCustomStyleDescriber(): CustomStyleDescriber
    {
        if ($this->customStyleDescriber === null) {
            $this->customStyleDescriber = app(CustomStyleDescriber::class);
        }

        return $this->customStyleDescriber;
    }

    public function includeContent(ApiArea $area)
    {
        return new Item($area, new AreaContentTransformer());
    }
}
