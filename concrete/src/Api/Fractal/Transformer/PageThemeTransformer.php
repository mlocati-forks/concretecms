<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Container\ContainerTemplates;
use Concrete\Core\Api\Model\PageTheme as PageThemeModel;
use Concrete\Core\Api\Model\PageThemeGrid;
use Concrete\Core\Api\Model\PageThemeImageBreakpoint;
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
     * @return array<string,mixed>
     */
    public function transform(PageTheme $theme): array
    {
        $model = new PageThemeModel();
        $model->handle = (string) $theme->getThemeHandle();
        $model->name = (string) $theme->getThemeName();
        $model->description = (string) $theme->getThemeDescription();
        $model->package = (string) $theme->getPackageHandle();
        $model->grid = $this->describeGrid($theme);
        $model->image_breakpoints = $this->describeImageBreakpoints($theme);
        $model->presets = $this->describePresets($theme);
        $model->containers = $this->describeContainers($theme);

        return $model->jsonSerialize();
    }

    /**
     * @return \Concrete\Core\Api\Model\PageThemeImageBreakpoint[]
     */
    protected function describeImageBreakpoints(PageTheme $theme): array
    {
        $breakpoints = [];
        foreach ($theme->getThemeResponsiveImageMap() as $handle => $minimumWidth) {
            $model = new PageThemeImageBreakpoint();
            $model->handle = (string) $handle;
            $model->minimum_width = (string) $minimumWidth;
            $breakpoints[] = $model;
        }

        return $breakpoints;
    }

    /**
     * @return \Concrete\Core\Api\Model\Container[]
     */
    protected function describeContainers(PageTheme $theme): array
    {
        $containerTransformer = new ContainerTransformer($this->containerTemplates);
        $containers = [];
        foreach ($this->containerTemplates->getContainersOfTheme($theme) as $container) {
            $containers[] = $containerTransformer->describe($container);
        }

        return $containers;
    }

    /**
     * @return \Concrete\Core\Api\Model\LayoutPreset[]
     */
    protected function describePresets(PageTheme $theme): array
    {
        if (!$theme instanceof ThemeProviderInterface) {
            return [];
        }
        $presetTransformer = new LayoutPresetTransformer((string) $theme->getThemeHandle());
        $presets = [];
        foreach ((new ThemeProvider($theme))->getPresets() as $preset) {
            $presets[] = $presetTransformer->describe($preset);
        }

        return $presets;
    }

    /**
     * @return \Concrete\Core\Api\Model\PageThemeGrid|null NULL when the theme declares no grid framework
     */
    private function describeGrid(PageTheme $theme): ?PageThemeGrid
    {
        $grid = $theme->getThemeGridFrameworkObject();
        if ($grid === null) {
            return null;
        }

        $model = new PageThemeGrid();
        $model->handle = (string) $theme->getThemeGridFrameworkHandle();
        $model->name = (string) $grid->getPageThemeGridFrameworkName();
        $model->columns = (int) $grid->getPageThemeGridFrameworkNumColumns();
        $model->supports_nesting = (bool) $grid->supportsNesting();
        $model->supports_offsets = (bool) $grid->hasPageThemeGridFrameworkOffsetClasses();

        return $model;
    }
}
