<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Area\Layout\Preset\Provider\ThemeProvider;
use Concrete\Core\Area\Layout\Preset\Provider\ThemeProviderInterface;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class PageThemeTransformer extends TransformerAbstract
{
    /**
     * Get what the API hands to its clients for a page theme.
     *
     * @return array<string,mixed>
     */
    public function transform(PageTheme $theme): array
    {
        return [
            'handle' => (string) $theme->getThemeHandle(),
            'name' => (string) $theme->getThemeName(),
            'description' => (string) $theme->getThemeDescription(),
            'package' => (string) $theme->getPackageHandle(),
            'grid' => $this->getGrid($theme),
            'presets' => $this->getPresets($theme),
        ];
    }

    /**
     * Get the ready-made layouts that a theme offers.
     *
     * @return array<int,array<string,mixed>>
     */
    protected function getPresets(PageTheme $theme): array
    {
        if (!$theme instanceof ThemeProviderInterface) {
            return [];
        }
        $presetTransformer = new LayoutPresetTransformer((string) $theme->getThemeHandle());
        $presets = [];
        foreach ((new ThemeProvider($theme))->getPresets() as $preset) {
            $presets[] = $presetTransformer->transform($preset);
        }

        return $presets;
    }

    /**
     * Get the grid framework that the layouts of the pages of a theme are sized with.
     *
     * @return array<string,mixed>|null NULL when the theme declares no grid framework
     */
    private function getGrid(PageTheme $theme): ?array
    {
        $grid = $theme->getThemeGridFrameworkObject();
        if ($grid === null) {
            return null;
        }

        return [
            'handle' => (string) $theme->getThemeGridFrameworkHandle(),
            'name' => (string) $grid->getPageThemeGridFrameworkName(),
            'columns' => (int) $grid->getPageThemeGridFrameworkNumColumns(),
            'supports_nesting' => (bool) $grid->supportsNesting(),
            'supports_offsets' => (bool) $grid->hasPageThemeGridFrameworkOffsetClasses(),
        ];
    }
}
