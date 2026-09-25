<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="BlockType model",
 * )
 */
class BlockType
{
    /**
     * @OA\Property(type="string", title="Block Type Handle")
     *
     * @var string
     */
    private $handle;

    /**
     * @OA\Property(type="string", title="Block Type Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="string", title="Block Type Description")
     *
     * @var string
     */
    private $description;

    /**
     * @OA\Property(type="string", nullable=true, title="Handle of the package providing the block type")
     *
     * @var string|null
     */
    private $package;

    /**
     * @OA\Property(type="object", title="Block value schema", description="The JSON Schema of the value that the areas endpoints accept for a block of this type")
     *
     * @var array
     */
    private $value_schema;
}
