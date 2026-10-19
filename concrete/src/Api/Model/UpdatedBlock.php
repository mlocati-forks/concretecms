<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     title="UpdatedBlock",
 *     description="A Concrete Block",
 *     required={"value"}
*     )
 */
class UpdatedBlock
{

    /**
     * @OA\Property(type="object", title="Block value", description="Key/Value object that maps to the request array that powers the block editing interface.")
     *
     * @var string
     */
    private $value;

    /**
     * @OA\Property(ref="#/components/schemas/CustomStyle", nullable=true, title="Custom style", description="Custom style to give it beyond what the theme of the page does. NULL, or a custom style with nothing in it that holds, leaves it to the theme alone")
     *
     * @var \Concrete\Core\Api\Model\CustomStyle|null
     */
    private $custom_style;

    /**
     * @OA\Property(type="string", title="Custom template", description="The handle of one of the custom_templates that GET /block_types gives for its type, empty for the default view of its type. Leave it out to keep the one the block has.")
     *
     * @var string
     */
    private $custom_template;




}
