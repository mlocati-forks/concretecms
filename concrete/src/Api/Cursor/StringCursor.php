<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Cursor;

use Concrete\Core\Api\Cursor;
use Concrete\Core\Http\Request;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="StringCursorMeta",
 *     type="object",
 *     title="StringCursorMeta",
 *     description="What a list walked by the UUID of its objects answers besides them",
 *     @OA\Property(
 *         property="cursor",
 *         type="object",
 *         title="Cursor",
 *         description="Where the list was walked, and where to walk it on",
 *         @OA\Property(property="current", type="string", nullable=true, title="Current", description="Where this answer was asked to start at, NULL for the beginning of the list"),
 *         @OA\Property(property="prev", type="string", nullable=true, title="Previous", description="Always NULL: this API does not walk a list backwards"),
 *         @OA\Property(property="next", type="string", nullable=true, title="Next", description="UUID to ask the list after, NULL where it ends here"),
 *         @OA\Property(property="count", type="integer", title="Count", description="How many objects this answer carries")
 *     )
 * )
 */
final class StringCursor extends Cursor
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Cursor::getCurrent()
     */
    public function getCurrent(Request $request): ?string
    {
        $after = $request->query->get('after');

        return $after === null || $after === '' ? null : (string) $after;
    }
}
