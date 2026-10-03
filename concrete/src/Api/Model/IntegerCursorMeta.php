<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="IntegerCursorMeta model",
 * )
 */
class IntegerCursorMeta
{
    /**
     * @OA\Property(
     *     type="object",
     *     title="Where the list was walked, and where to walk it on",
     *     @OA\Property(property="current", type="integer", nullable=true, title="Where this answer was asked to start at, NULL for the beginning of the list"),
     *     @OA\Property(property="prev", type="integer", nullable=true, title="Always NULL: this API does not walk a list backwards"),
     *     @OA\Property(property="next", type="integer", nullable=true, title="ID to ask the list after, NULL where it ends here"),
     *     @OA\Property(property="count", type="integer", title="How many objects this answer carries")
     * )
     *
     * @var array
     */
    private $cursor;
}
