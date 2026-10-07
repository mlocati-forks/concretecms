<?php
namespace Concrete\Core\Api\Fractal\Transformer\Traits;

use Concrete\Core\Area\ApiArea;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Page\Page;

trait GetPageApiAreasTrait
{

    /**
     * Get the areas a page has, the empty ones included: a client places a block in one of these.
     *
     * An area of a page is a record created by whatever drew it, so a page that nothing has drawn yet
     * has none to hand over: POST /pages/{pageID}/areas/refresh draws it.
     *
     * @param bool $onlyWithBlocks true to leave out the areas that hold no block in this version of the page
     *
     * @return \Concrete\Core\Area\ApiArea[]
     */
    public function getAreas(Page $page, bool $onlyWithBlocks = false): array
    {
        $connection = app(Connection::class);
        $handles = $onlyWithBlocks
            ? $connection->fetchFirstColumn(
                'SELECT DISTINCT arHandle FROM CollectionVersionBlocks WHERE cID = ? AND cvID = ? ORDER BY arHandle',
                [$page->getCollectionID(), $page->getVersionID()]
            )
            : $connection->fetchFirstColumn(
                'SELECT arHandle FROM Areas WHERE cID = ? ORDER BY arHandle',
                [$page->getCollectionID()]
            );

        $areas = [];
        foreach ($handles as $handle) {
            $areas[] = new ApiArea($page, $handle);
        }

        return $areas;
    }
}
