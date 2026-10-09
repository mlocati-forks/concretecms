<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeKey\Select;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeKeySelectOption",
 *     type="object",
 *     title="One of the options a value of an attribute key is picked out of",
 * )
 */
class Option implements \JsonSerializable
{
    /**
     * @OA\Property(
     *     format="int64",
     *     title="ID of the option",
     *     description="What a value of the key is written with"
     * )
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(title="Value of the option")
     *
     * @var string
     */
    public $value;

    /**
     * @OA\Property(title="Value of the option as it is displayed, which a translation of the site may change")
     *
     * @var string
     */
    public $display_value;

    /**
     * {@inheritdoc}
     *
     * @see \JsonSerializable::jsonSerialize()
     *
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return (array) $this;
    }
}
