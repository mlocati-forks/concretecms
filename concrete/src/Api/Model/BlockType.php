<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="BlockType model",
 * )
 */
class BlockType implements \JsonSerializable
{
    /**
     * @OA\Property(title="Block Type Handle")
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(title="Block Type Name")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Block Type Description")
     *
     * @var string
     */
    public $description;

    /**
     * @OA\Property(title="Handle of the package providing the block type, empty when it belongs to none")
     *
     * @var string
     */
    public $package;

    /**
     * @OA\Property(type="object", title="Block value schema", description="The JSON Schema of the value that the areas endpoints accept for a block of this type")
     *
     * @var array
     */
    public $value_schema;

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
