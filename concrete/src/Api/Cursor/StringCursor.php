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
 *     title="What a list walked by the UUID of its objects answers besides them",
 *     @OA\Property(
 *         property="cursor",
 *         type="object",
 *         title="Where the list was walked, and where to walk it on",
 *         @OA\Property(property="current", type="string", nullable=true, title="Where this answer was asked to start at, NULL for the beginning of the list"),
 *         @OA\Property(property="prev", type="string", nullable=true, title="Always NULL: this API does not walk a list backwards"),
 *         @OA\Property(property="next", type="string", nullable=true, title="UUID to ask the list after, NULL where it ends here"),
 *         @OA\Property(property="count", type="integer", title="How many objects this answer carries")
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
