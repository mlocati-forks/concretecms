<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     title="Block",
 * )
 */
class Block
{

    /**
     * @OA\Property(type="integer", format="int64", title="Block ID")
     *
     * @var string
     */
    private $id;

    /**
     * @OA\Property(type="string", format="string", title="Block Type Handle")
     *
     * @var string
     */
    private $type;

    /**
     * @OA\Property(type="string", title="Custom template", description="The handle of one of the custom_templates that GET /block_types gives for its type, empty when the block is shown with the default view of its type")
     *
     * @var string
     */
    private $custom_template;

    /**
     * @OA\Property(ref="#/components/schemas/CustomStyle", nullable=true, title="Custom style", description="Custom style given to it beyond what the theme of the page does, NULL where it has none of its own")
     *
     * @var \Concrete\Core\Api\Model\CustomStyle|null
     */
    private $custom_style;

    /**
     * @OA\Property(type="object", title="Block value")
     *
     * @var string
     */
    private $value;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Page",
     *     description="Page the block sits in, which the answers of the area endpoints carry",
     *     @OA\Property(
     *         property="data",
     *         ref="#/components/schemas/Page"
     *     )
     * )
     *
     * @var array
     */
    private $page;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Pages",
     *     description="Pages the block sits in, where the includes parameter asks for them",
     *     @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/Page")
     *     )
     * )
     *
     * @var array
     */
    private $pages;
}
