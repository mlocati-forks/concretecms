<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="PageThemeGrid model",
 * )
 */
class PageThemeGrid implements \JsonSerializable
{
    /**
     * @OA\Property(title="Grid Framework Handle")
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(title="Grid Framework Name")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Number of columns the grid is divided in", description="The span and the offset of the columns of a theme-grid layout are counted in these")
     *
     * @var int
     */
    public $columns;

    /**
     * @OA\Property(title="Whether a layout can sit in the column of another layout")
     *
     * @var bool
     */
    public $supports_nesting;

    /**
     * @OA\Property(title="Whether the columns of a layout can be preceded by empty ones", description="The offset of a column goes nowhere when the grid has no offsets")
     *
     * @var bool
     */
    public $supports_offsets;

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
