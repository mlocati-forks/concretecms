<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\PageTheme as PageThemeModel;
use Concrete\Core\Api\Model\PageThemeGrid;
use Concrete\Core\Area\Layout\Preset\Provider\ThemeProvider;
use Concrete\Core\Area\Layout\Preset\Provider\ThemeProviderInterface;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class PageThemeTransformer extends TransformerAbstract
{
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
        $model->grid = $this->getGrid($theme);
        $model->presets = $this->getPresets($theme);

        return $model->jsonSerialize();
    }

    /**
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
     * @return array<string,mixed>|null NULL when the theme declares no grid framework
     */
    private function getGrid(PageTheme $theme): ?array
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

        return $model->jsonSerialize();
    }
}
