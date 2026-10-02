<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Theme;

use Concrete\Core\Api\Fractal\Transformer\PageThemeTransformer;
use Concrete\Core\Area\Layout\Preset\Provider\ThemeProviderInterface;
use Concrete\Core\Page\Theme\GridFramework\GridFramework;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests what the page themes endpoint hands to its clients.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\PageThemeTransformer
 */
class PageThemeTransformerTest extends TestCase
{
    public function testAPageThemeIsDescribedWithTheGridFrameworkItDeclares(): void
    {
        $grid = $this->createMock(GridFramework::class);
        $grid->method('getPageThemeGridFrameworkName')->willReturn('Bootstrap 5');
        $grid->method('getPageThemeGridFrameworkNumColumns')->willReturn(12);
        $grid->method('supportsNesting')->willReturn(true);
        $grid->method('hasPageThemeGridFrameworkOffsetClasses')->willReturn(false);
        $theme = $this->createTheme('elemental', 'Elemental', 'bootstrap5', $grid);

        $transformed = (new PageThemeTransformer())->transform($theme);

        static::assertSame([
            'handle' => 'elemental',
            'name' => 'Elemental',
            'description' => 'Elegant, spacious theme',
            'package' => '',
            'grid' => [
                'handle' => 'bootstrap5',
                'name' => 'Bootstrap 5',
                'columns' => 12,
                'supports_nesting' => true,
                'supports_offsets' => false,
            ],
            'presets' => [],
        ], $transformed);
    }

    public function testAPageThemeDeclaringNoGridFrameworkHasNone(): void
    {
        $theme = $this->createTheme('plain', 'Plain', false, null);

        static::assertNull((new PageThemeTransformer())->transform($theme)['grid']);
    }

    public function testThePresetsOfAThemeTravelWithIt(): void
    {
        $theme = new class extends PageTheme implements ThemeProviderInterface {
            public function getThemeHandle()
            {
                return 'elemental';
            }

            public function getThemeName()
            {
                return 'Elemental';
            }

            public function getThemeAreaLayoutPresets()
            {
                return [
                    [
                        'handle' => 'left_sidebar',
                        'name' => 'Left Sidebar',
                        'container' => '<div class="row"></div>',
                        'columns' => ['<div class="col-sm-4"></div>', '<div class="col-sm-8"></div>'],
                    ],
                ];
            }
        };

        $transformed = (new PageThemeTransformer())->transform($theme);

        static::assertSame([
            ['identifier' => 'theme_elemental_left_sidebar', 'name' => 'Left Sidebar', 'columns' => 2, 'theme' => 'elemental'],
        ], $transformed['presets']);
    }

    /**
     * @param string|false $gridHandle the handle of the grid framework, FALSE when the theme declares none
     */
    private function createTheme(string $handle, string $name, $gridHandle, ?GridFramework $grid): PageTheme
    {
        $theme = $this->createMock(PageTheme::class);
        $theme->method('getThemeHandle')->willReturn($handle);
        $theme->method('getThemeName')->willReturn($name);
        $theme->method('getThemeDescription')->willReturn('Elegant, spacious theme');
        // the method returns FALSE when the theme belongs to no package
        $theme->method('getPackageHandle')->willReturn(false);
        $theme->method('getThemeGridFrameworkHandle')->willReturn($gridHandle);
        $theme->method('getThemeGridFrameworkObject')->willReturn($grid);

        return $theme;
    }
}
