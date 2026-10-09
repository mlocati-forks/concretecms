<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeKey;

use Concrete\Core\Api\Model\AttributeKey;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeKeyDuration",
 *     type="object",
 *     title="A key whose value is a duration",
 *     allOf={@OA\Schema(ref="#/components/schemas/AttributeKey")}
 * )
 */
class Duration extends AttributeKey
{
    /**
     * @OA\Property(
     *     title="Unit a value of this key is written in",
     *     description="One of seconds, minutes, hours, days, weeks, months, years",
     *     enum={"seconds", "minutes", "hours", "days", "weeks", "months", "years"}
     * )
     *
     * @var string
     */
    public $unit;
}
