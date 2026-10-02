<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Layout;

use Concrete\Core\Api\Fractal\Transformer\LayoutPresetTransformer;
use Concrete\Core\Area\Layout\ColumnInterface;
use Concrete\Core\Area\Layout\Preset\PresetInterface;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\Fractal\Transformer\LayoutPresetTransformer
 */
class LayoutPresetTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testAPresetOfAThemeSaysWhichThemeOffersIt(): void
    {
        // a theme names its presets after itself
        $preset = $this->createPreset('theme_elemental_left_sidebar', 'Left Sidebar', 2);

        $transformed = (new LayoutPresetTransformer('elemental'))->transform($preset);

        static::assertSame([
            'identifier' => 'theme_elemental_left_sidebar',
            'name' => 'Left Sidebar',
            'columns' => 2,
            'theme' => 'elemental',
        ], $transformed);
        static::assertSame($this->getSchemaFields('LayoutPreset'), array_keys($transformed));
    }

    public function testAPresetSavedOnTheSiteIsNamedByTheIdOfItsLayout(): void
    {
        $preset = $this->createPreset(64, 'Ours', 3);

        $transformed = (new LayoutPresetTransformer())->transform($preset);

        static::assertSame('64', $transformed['identifier']);
        static::assertSame('', $transformed['theme']);
    }

    /**
     * @param int|string $identifier
     */
    private function createPreset($identifier, string $name, int $columns): PresetInterface
    {
        $preset = $this->createMock(PresetInterface::class);
        $preset->method('getIdentifier')->willReturn($identifier);
        $preset->method('getName')->willReturn($name);
        $preset->method('getColumns')->willReturn(array_fill(0, $columns, $this->createMock(ColumnInterface::class)));

        return $preset;
    }
}
