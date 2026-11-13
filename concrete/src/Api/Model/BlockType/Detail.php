<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\BlockType;

use Concrete\Core\Api\Model\BlockType;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="BlockTypeDetail",
 *     type="object",
 *     title="BlockTypeDetail",
 *     description="A block type asked for by itself, which is the only answer carrying the schema of the value of its blocks",
 *     allOf={@OA\Schema(ref="#/components/schemas/BlockType")}
 * )
 */
class Detail extends BlockType
{
    /**
     * @OA\Property(type="object", nullable=true, title="Block value schema", description="The JSON Schema of the value that the areas endpoints accept for a block of this type. It is NULL where this installation holds the block type but not its code any more, the one case where nothing at all is known about the value: a block type that builds its value by itself answers with a schema marked x-concrete-undescribed instead, and the list of the block types leaves this field out altogether")
     *
     * @var array|null
     */
    public $value_schema;
}
