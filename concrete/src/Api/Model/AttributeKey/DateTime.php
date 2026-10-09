<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeKey;

use Concrete\Core\Api\Model\AttributeKey;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeKeyDateTime",
 *     type="object",
 *     title="A key whose value is a moment in time",
 *     allOf={@OA\Schema(ref="#/components/schemas/AttributeKey")}
 * )
 */
class DateTime extends AttributeKey
{
    /**
     * @OA\Property(
     *     title="What the key is meant to hold",
     *     description="A key of the date modes means the time of its values to be ignored. Whatever the mode, a value is read as any date that PHP parses and comes back as Y-m-d H:i:s.",
     *     enum={"date_time", "date", "text", "date_text"}
     * )
     *
     * @var string
     */
    public $mode;
}
