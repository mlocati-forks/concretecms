<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="LayoutPreset model",
 * )
 */
class LayoutPreset implements \JsonSerializable
{
    /**
     * @OA\Property(title="Layout Preset Identifier", description="What the preset field of a core_area_layout block takes")
     *
     * @var string
     */
    public $identifier;

    /**
     * @OA\Property(title="Layout Preset Name")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Number of columns the preset lays out")
     *
     * @var int
     */
    public $columns;

    /**
     * @OA\Property(title="Handle of the page theme offering the layout, empty when the users of this installation defined it", description="A layout of a page theme can only be given to the pages shown with that theme, while the ones defined here fit any")
     *
     * @var string
     */
    public $page_theme;

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
