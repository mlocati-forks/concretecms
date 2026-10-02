<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="LayoutPreset model",
 * )
 */
class LayoutPreset
{
    /**
     * @OA\Property(type="string", title="Layout Preset Identifier", description="What the preset field of a core_area_layout block takes")
     *
     * @var string
     */
    private $identifier;

    /**
     * @OA\Property(type="string", title="Layout Preset Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="integer", title="Number of columns the preset lays out")
     *
     * @var int
     */
    private $columns;

    /**
     * @OA\Property(type="string", title="Handle of the theme offering the layout, empty when the users of this installation defined it", description="A layout of a theme can only be given to the pages shown with that theme, while the ones defined here fit any")
     *
     * @var string
     */
    private $theme;
}
