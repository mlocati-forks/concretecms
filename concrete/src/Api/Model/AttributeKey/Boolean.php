<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeKey;

use Concrete\Core\Api\Model\AttributeKey;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeKeyBoolean",
 *     type="object",
 *     title="A key whose value is checked or not",
 *     allOf={@OA\Schema(ref="#/components/schemas/AttributeKey")}
 * )
 */
class Boolean extends AttributeKey
{
    /**
     * @OA\Property(title="Whether an object that never had a value of this key counts as checked")
     *
     * @var bool
     */
    public $checked_by_default;
}
