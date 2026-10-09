<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="AttributeKey model",
 *     description="An attribute of the objects of a category, which a client reads and writes among their custom_attributes. The type of a key and its category may each add fields of their own, described by a schema of their own, and a package brings the ones it wants.",
 * )
 */
class AttributeKey implements \JsonSerializable
{
    /**
     * @OA\Property(
     *     format="int64",
     *     title="ID of the key",
     *     description="What the blocks naming an attribute of an Express entity name"
     * )
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(
     *     title="Handle of the key",
     *     description="What the custom_attributes of an object are keyed by"
     * )
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(title="Name of the key, as the dashboard shows it")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Handle of the type of the key, which settles what a value of it looks like")
     *
     * @var string
     */
    public $type;

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
