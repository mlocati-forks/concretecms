<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Theme;

use Concrete\Core\Api\Container\ContainerTemplates;
use Concrete\Core\Api\Fractal\Transformer\PageThemeTransformer;
use Concrete\Core\Area\Layout\Preset\Provider\ThemeProviderInterface;
use Concrete\Core\Entity\Page\Container;
use Concrete\Core\Page\Theme\GridFramework\GridFramework;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\Fractal\Transformer\PageThemeTransformer
 */
class PageThemeTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testAPageThemeIsDescribedWithTheGridFrameworkItDeclares(): void
    {
        $grid = $this->createMock(GridFramework::class);
        $grid->method('getPageThemeGridFrameworkName')->willReturn('Bootstrap 5');
        $grid->method('getPageThemeGridFrameworkNumColumns')->willReturn(12);
        $grid->method('supportsNesting')->willReturn(true);
        $grid->method('hasPageThemeGridFrameworkOffsetClasses')->willReturn(false);
        $theme = $this->createTheme('elemental', 'Elemental', 'bootstrap5', $grid);

        $transformed = $this->createTransformer()->transform($theme);

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
            'image_breakpoints' => [],
            'presets' => [],
            'containers' => [],
        ], $transformed);
        $this->assertFieldsAre('PageTheme', $transformed);
    }

    public function testAPageThemeDeclaringNoGridFrameworkHasNone(): void
    {
        $theme = $this->createTheme('plain', 'Plain', false, null);

        static::assertNull($this->createTransformer()->transform($theme)['grid']);
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

        $transformed = $this->createTransformer()->transform($theme);

        static::assertSame([
            ['identifier' => 'theme_elemental_left_sidebar', 'name' => 'Left Sidebar', 'columns' => 2, 'page_theme' => 'elemental'],
        ], $transformed['presets']);
    }

    public function testTheImageBreakpointsOfAThemeTravelWithItInTheOrderItDeclaresThem(): void
    {
        $theme = $this->createTheme('atomik', 'Atomik', 'bootstrap5', null);
        $theme->method('getThemeResponsiveImageMap')->willReturn(['lg' => '992px', 'md' => '768px', 'xs' => '0']);

        $transformed = $this->createTransformer()->transform($theme);

        static::assertSame([
            ['handle' => 'lg', 'minimum_width' => '992px'],
            ['handle' => 'md', 'minimum_width' => '768px'],
            ['handle' => 'xs', 'minimum_width' => '0'],
        ], $transformed['image_breakpoints']);
    }

    public function testTheContainersAThemeCanShowTravelWithIt(): void
    {
        $theme = $this->createTheme('atomik', 'Atomik', 'bootstrap5', null);
        $container = (new Container())->setContainerHandle('light_stripe')->setContainerName('Highlight Stripe');

        $transformed = $this->createTransformer([$container])->transform($theme);

        static::assertSame([
            ['handle' => 'light_stripe', 'name' => 'Highlight Stripe', 'package' => '', 'page_themes' => [], 'application' => false],
        ], $transformed['containers']);
    }

    /**
     * @param \Concrete\Core\Entity\Page\Container[] $containersOfTheTheme
     */
    private function createTransformer(array $containersOfTheTheme = []): PageThemeTransformer
    {
        $containerTemplates = $this->createMock(ContainerTemplates::class);
        $containerTemplates->method('getContainersOfTheme')->willReturn($containersOfTheTheme);

        return new PageThemeTransformer($containerTemplates);
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
