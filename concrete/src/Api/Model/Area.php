<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     title="Area model",
 * )
 */
class Area
{

    /**
     * @OA\Property(type="string", format="string", title="Block Area")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Blocks of the area, which every answer carries",
     *     @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/Block")
     *     )
     * )
     *
     * @var array
     */
    private $blocks;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Content of the area, where the includes parameter asks for it",
     *     @OA\Property(
     *         property="data",
     *         ref="#/components/schemas/Content"
     *     )
     * )
     *
     * @var array
     */
    private $content;




}