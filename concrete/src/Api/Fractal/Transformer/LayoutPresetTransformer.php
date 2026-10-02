<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Area\Layout\Preset\PresetInterface;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class LayoutPresetTransformer extends TransformerAbstract
{
    /**
     * @var string
     */
    protected $themeHandle;

    /**
     * @param string $themeHandle the handle of the theme offering the presets, empty for the ones that the users of this installation defined
     */
    public function __construct(string $themeHandle = '')
    {
        $this->themeHandle = $themeHandle;
    }

    /**
     * Get what the API hands to its clients for a layout preset.
     *
     * @return array<string,mixed>
     */
    public function transform(PresetInterface $preset): array
    {
        return [
            // a preset saved on the site is named by the ID of its layout, a theme one by a string
            'identifier' => (string) $preset->getIdentifier(),
            'name' => (string) $preset->getName(),
            'columns' => count($preset->getColumns()),
            'page_theme' => $this->themeHandle,
        ];
    }
}
