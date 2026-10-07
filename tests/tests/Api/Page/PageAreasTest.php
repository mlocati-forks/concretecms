<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Page;

use Concrete\Core\Api\Fractal\Transformer\PageTransformer;
use Concrete\Core\Area\Area;
use Concrete\Core\Page\Page;
use Concrete\TestHelpers\Block\BlockApiTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A client places a block in one of the areas a page answers with, so an area holding none has to be
 * there as well.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\Traits\GetPageApiAreasTrait::getAreas()
 */
class PageAreasTest extends BlockApiTestCase
{
    public function testEveryAreaOfThePageIsAnswered(): void
    {
        $page = $this->createPageWithAnEmptyArea();

        static::assertSame(['Main', 'Sidebar'], $this->getAreaHandles($page));
    }

    public function testTheAreasHoldingABlockAreAnsweredWhereTheRequestAsksForThose(): void
    {
        $page = $this->createPageWithAnEmptyArea();

        static::assertSame(['Main'], $this->getAreaHandles($page, true));
    }

    /**
     * @return string[]
     */
    private function getAreaHandles(Page $page, bool $onlyWithBlocks = false): array
    {
        $handles = [];
        foreach ((new PageTransformer())->getAreas($page, $onlyWithBlocks) as $area) {
            $handles[] = $area->getAreaHandle();
        }

        return $handles;
    }

    private function createPageWithAnEmptyArea(): Page
    {
        $page = self::createPage('Page with an empty area');
        Area::getOrCreate($page, 'Sidebar');
        $page->addBlock(
            $this->getBlockType('content'),
            Area::getOrCreate($page, 'Main'),
            ['content' => 'Something']
        );

        return Page::getByID($page->getCollectionID(), 'RECENT');
    }
}
