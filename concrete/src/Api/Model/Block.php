<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     title="Block model",
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
     * @OA\Property(type="object", title="Block value")
     *
     * @var string
     */
    private $value;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Page the block sits in, which the answers of the area endpoints carry",
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
     *     title="Pages the block sits in, where the includes parameter asks for them",
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
