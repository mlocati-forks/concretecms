<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     title="NewBlock model",
 *     description="A Concrete Block",
 *     required={"type"}
 * )
 */
class NewBlock
{

    /**
     * @OA\Property(type="string", title="Block Type Handle")
     *
     * @var string
     */
    private $type;

    /**
     * @OA\Property(type="object", title="Block value", description="Key/Value object that maps to the request array that powers the block editing interface.")
     *
     * @var string
     */
    private $value;

    /**
     * @OA\Property(type="string", title="Custom template of the block", description="The handle of one of the custom_templates that GET /block_types gives for its type: left out, or empty, the new block is shown with the default view of its type.")
     *
     * @var string
     */
    private $custom_template;

    /**
     * @OA\Property(type="integer", format="int64", title="Insert before block", description="The ID of a block already in the same area: the new block is placed just before it. Without it, the new block goes at the end of the area.")
     *
     * @var int|null
     */
    private $beforeBlockID;



}
