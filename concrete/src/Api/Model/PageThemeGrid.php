<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="PageThemeGrid model",
 * )
 */
class PageThemeGrid
{
    /**
     * @OA\Property(type="string", title="Grid Framework Handle")
     *
     * @var string
     */
    private $handle;

    /**
     * @OA\Property(type="string", title="Grid Framework Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="integer", title="Number of columns the grid is divided in", description="The span and the offset of the columns of a theme-grid layout are counted in these")
     *
     * @var int
     */
    private $columns;

    /**
     * @OA\Property(type="boolean", title="Whether a layout can sit in the column of another layout")
     *
     * @var bool
     */
    private $supports_nesting;

    /**
     * @OA\Property(type="boolean", title="Whether the columns of a layout can be preceded by empty ones", description="The offset of a column goes nowhere when the grid has no offsets")
     *
     * @var bool
     */
    private $supports_offsets;
}
