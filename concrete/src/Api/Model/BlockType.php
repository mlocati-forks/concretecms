<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="BlockType",
 *     description="What the list of the block types says about one of them",
 * )
 *
 * @see \Concrete\Core\Api\Model\BlockType\Detail
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
     * @OA\Property(title="Package", description="Handle of the package providing the block type, empty when it belongs to none")
     *
     * @var string
     */
    public $package;

    /**
     * @OA\Property(
     *     type="array",
     *     title="Custom templates",
     *     description="Custom templates a block of this type can be shown with",
     *     @OA\Items(ref="#/components/schemas/BlockTypeCustomTemplate")
     * )
     *
     * @var \Concrete\Core\Api\Model\BlockType\CustomTemplate[]
     */
    public $custom_templates = [];

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
