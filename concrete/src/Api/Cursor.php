<?php

declare(strict_types=1);

namespace Concrete\Core\Api;

use Concrete\Core\Http\Request;
use Concrete\Core\Search\Column\PagerColumnInterface;
use Concrete\Core\Search\ItemList\Pager\PagerProviderInterface;
use League\Fractal\Pagination\Cursor as FractalCursor;
use League\Fractal\Resource\ResourceAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A list comes a page at a time, and a client asks for the next one with the after parameter, naming the last
 * object it holds.
 *
 * @see \Concrete\Core\Api\Cursor\IntegerCursor
 * @see \Concrete\Core\Api\Cursor\StringCursor
 */
abstract class Cursor
{
    /**
     * @var \Closure
     */
    private $getKey;

    /**
     * @param \Closure $getKey takes an object of the list and answers with its key
     */
    public function __construct(\Closure $getKey)
    {
        $this->getKey = $getKey;
    }

    /**
     * @return int|string|null NULL where the request asks for the beginning of the list
     */
    abstract public function getCurrent(Request $request);

    /**
     * Where the request names an object of the list, leave out what the column it is sorted by puts
     * before that one.
     *
     * @param callable $getObject takes the key the request sent and answers with the object it names,
     *                            or with NULL where the list holds no such object
     */
    public function startAfter(Request $request, PagerProviderInterface $list, PagerColumnInterface $column, callable $getObject): void
    {
        $current = $this->getCurrent($request);
        if ($current === null) {
            return;
        }
        $object = $getObject($current);
        if ($object === null) {
            return;
        }
        $column->filterListAtOffset($list, $object);
    }

    /**
     * @param object[] $results the objects of this page, in the order the answer carries them
     */
    public function describe(Request $request, array $results, ResourceAbstract $resource): void
    {
        $count = count($results);
        $resource->setCursor(new FractalCursor(
            $this->getCurrent($request),
            // this API does not walk a list backwards
            null,
            $count === 0 ? null : ($this->getKey)($results[array_key_last($results)]),
            $count
        ));
    }
}
