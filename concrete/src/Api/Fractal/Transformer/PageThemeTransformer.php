<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Container\ContainerTemplates;
use Concrete\Core\Area\Layout\Preset\Provider\ThemeProvider;
use Concrete\Core\Area\Layout\Preset\Provider\ThemeProviderInterface;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class PageThemeTransformer extends TransformerAbstract
{
    /**
     * @var \Concrete\Core\Api\Container\ContainerTemplates
     */
    protected $containerTemplates;

    public function __construct(ContainerTemplates $containerTemplates)
    {
        $this->containerTemplates = $containerTemplates;
    }

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
            'containers' => $this->getContainers($theme),
        ];
    }

    /**
     * Get the containers that a theme carries the template of.
     *
     * @return array<int,array<string,mixed>>
     */
    protected function getContainers(PageTheme $theme): array
    {
        $containerTransformer = new ContainerTransformer($this->containerTemplates);
        $containers = [];
        foreach ($this->containerTemplates->getContainersOfTheme($theme) as $container) {
            $containers[] = $containerTransformer->transform($container);
        }

        return $containers;
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
