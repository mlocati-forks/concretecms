<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeKey;

use Concrete\Core\Api\Model\AttributeKey;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeKeySelect",
 *     type="object",
 *     title="A key whose value is picked out of a fixed set",
 *     allOf={@OA\Schema(ref="#/components/schemas/AttributeKey")}
 * )
 */
class Select extends AttributeKey
{
    /**
     * The fields of the AttributeKeySelectOption model, which the handler of the type fills.
     *
     * @OA\Property(
     *     type="array",
     *     title="Options a value of this key is picked out of",
     *     description="A value names them by ID",
     *     @OA\Items(ref="#/components/schemas/AttributeKeySelectOption")
     * )
     */
    public $options;

    /**
     * @OA\Property(title="Whether a value of this key may name more than one option")
     *
     * @var bool
     */
    public $allow_multiple_values;

    /**
     * @OA\Property(
     *     title="Whether a value of this key may name an option the key hasn't got",
     *     description="A value of its own is written as a string, and the key keeps it among its options"
     * )
     *
     * @var bool
     */
    public $allow_other_values;
}
