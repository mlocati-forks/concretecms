<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Cursor;

use Concrete\Core\Api\Cursor;
use Concrete\Core\Http\Request;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="IntegerCursorMeta",
 *     type="object",
 *     title="IntegerCursorMeta",
 *     description="What a list walked by the ID of its objects answers besides them",
 *     @OA\Property(
 *         property="cursor",
 *         type="object",
 *         title="Cursor",
 *         description="Where the list was walked, and where to walk it on",
 *         @OA\Property(property="current", type="integer", nullable=true, title="Current", description="Where this answer was asked to start at, NULL for the beginning of the list"),
 *         @OA\Property(property="prev", type="integer", nullable=true, title="Previous", description="Always NULL: this API does not walk a list backwards"),
 *         @OA\Property(property="next", type="integer", nullable=true, title="Next", description="ID to ask the list after, NULL where it ends here"),
 *         @OA\Property(property="count", type="integer", title="Count", description="How many objects this answer carries")
 *     )
 * )
 */
final class IntegerCursor extends Cursor
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Cursor::getCurrent()
     */
    public function getCurrent(Request $request): ?int
    {
        // the ID of an object is a positive integer: anything else asks for the beginning of the list
        $after = filter_var($request->query->get('after'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $after === false ? null : $after;
    }
}
