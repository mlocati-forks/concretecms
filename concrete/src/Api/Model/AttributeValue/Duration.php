<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeValue;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeValueDuration",
 *     type="object",
 *     title="The duration a value of a duration attribute counts"
 * )
 */
class Duration
{
    /**
     * @OA\Property(
     *     type="integer",
     *     title="How many of them the attribute counts"
     * )
     *
     * @var int
     */
    public $value;

    /**
     * @OA\Property(
     *     type="string",
     *     title="The unit the key counts in",
     *     description="The key of the attribute names it, and a client writes the number alone"
     * )
     *
     * @var string
     */
    public $unit;
}
