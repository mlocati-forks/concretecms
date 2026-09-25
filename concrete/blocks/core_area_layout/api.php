<?php

declare(strict_types=1);

namespace Concrete\Block\CoreAreaLayout;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Area\Layout\CustomLayout;
use Concrete\Core\Area\Layout\Layout as AreaLayout;
use Concrete\Core\Area\Layout\Preset\Preset;
use Concrete\Core\Area\Layout\PresetLayout;
use Concrete\Core\Area\Layout\ThemeGridLayout;
use Concrete\Core\Block\Block;
use Concrete\Core\Error\UserMessageException;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The core_area_layout block type keeps in its table just the ID of the layout it adds to the page:
 * the value is the layout itself, with its columns and the areas that hold their blocks.
 */
class Api extends DefaultBlockApiHandler
{
    /**
     * The layout sizes its columns with the grid framework of the theme.
     *
     * @var string
     */
    private const TYPE_THEME_GRID = 'theme-grid';

    /**
     * The layout sizes its columns on its own.
     *
     * @var string
     */
    private const TYPE_CUSTOM = 'custom';

    /**
     * The layout is a ready-made one: a preset saved on this site, or one the theme offers.
     *
     * @var string
     */
    private const TYPE_PRESET = 'preset';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        return [
            'type' => 'object',
            'description' => 'The kind of the layout and the number of its columns are read when the block is added: afterwards only their sizes are.',
            'properties' => [
                'type' => [
                    'type' => 'string',
                    'enum' => [self::TYPE_THEME_GRID, self::TYPE_CUSTOM, self::TYPE_PRESET],
                    'description' => 'How the columns are sized: "' . self::TYPE_THEME_GRID . '" by the grid framework of the theme, "' . self::TYPE_CUSTOM . '" by the layout itself, "' . self::TYPE_PRESET . '" by the ready-made layout named by preset.',
                ],
                'maxColumns' => [
                    'type' => 'integer',
                    'description' => 'How many columns the grid of the theme is divided in (used if type is ' . self::TYPE_THEME_GRID . ').',
                ],
                'spacing' => [
                    'type' => 'integer',
                    'description' => 'The space between two columns, in pixels (used if type is ' . self::TYPE_CUSTOM . ').',
                ],
                'customWidths' => [
                    'type' => 'boolean',
                    'description' => 'true: every column is as wide as its own width says; false: the columns share the room evenly (used if type is ' . self::TYPE_CUSTOM . ').',
                ],
                'preset' => [
                    'type' => 'string',
                    'description' => 'The identifier of the ready-made layout, which is one of the presets saved on this site or one of the ones the theme offers (used if type is ' . self::TYPE_PRESET . ').',
                ],
                'columns' => [
                    'type' => 'array',
                    'description' => 'The columns of the layout, from left to right.',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'span' => [
                                'type' => 'integer',
                                'description' => 'How many columns of the grid of the theme the column takes (used if type is ' . self::TYPE_THEME_GRID . ').',
                            ],
                            'offset' => [
                                'type' => 'integer',
                                'description' => 'How many columns of the grid of the theme are left empty before the column (used if type is ' . self::TYPE_THEME_GRID . ').',
                            ],
                            'width' => [
                                'type' => 'integer',
                                'description' => 'How wide the column is, in pixels (used if type is ' . self::TYPE_CUSTOM . ' and customWidths is true).',
                            ],
                            'area' => [
                                'type' => 'string',
                                'readOnly' => true,
                                'description' => 'The handle of the area holding the blocks of the column, which the areas endpoints work with.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValue()
     */
    public function getApiValue(Block $block): array
    {
        $layout = $this->getLayout($block);
        $value = [
            'type' => $this->getLayoutType($layout),
            'maxColumns' => $layout instanceof ThemeGridLayout ? (int) $layout->getAreaLayoutMaxColumns() : 0,
            'spacing' => $layout instanceof CustomLayout ? (int) $layout->getAreaLayoutSpacing() : 0,
            'customWidths' => $layout instanceof CustomLayout && (bool) $layout->hasAreaLayoutCustomColumnWidths(),
            'preset' => $layout instanceof PresetLayout ? (string) $layout->getAreaLayoutPresetHandle() : '',
            'columns' => [],
        ];
        if ($layout === null) {
            return $value;
        }
        foreach ($layout->getAreaLayoutColumns() as $column) {
            $area = $column->getAreaObject();
            $value['columns'][] = [
                'span' => method_exists($column, 'getAreaLayoutColumnSpan') ? (int) $column->getAreaLayoutColumnSpan() : 0,
                'offset' => method_exists($column, 'getAreaLayoutColumnOffset') ? (int) $column->getAreaLayoutColumnOffset() : 0,
                'width' => method_exists($column, 'getAreaLayoutColumnWidth') ? (int) $column->getAreaLayoutColumnWidth() : 0,
                'area' => $area === null ? '' : (string) $area->getAreaHandle(),
            ];
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getSaveArgumentsFromApiValue()
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        $value += $block === null ? [] : $this->getApiValue($block);
        $columns = is_array($value['columns'] ?? null) ? $value['columns'] : [];
        switch ((string) ($value['type'] ?? '')) {
            case self::TYPE_THEME_GRID:
                $arguments = [
                    'gridType' => 'TG',
                    'arLayoutMaxColumns' => (int) ($value['maxColumns'] ?? 0),
                    'themeGridColumns' => count($columns),
                    'span' => [],
                    'offset' => [],
                ];
                foreach ($columns as $column) {
                    $arguments['span'][] = (int) ($column['span'] ?? 0);
                    $arguments['offset'][] = (int) ($column['offset'] ?? 0);
                }

                return $arguments;
            case self::TYPE_PRESET:
                $identifier = $this->getPresetIdentifier((string) ($value['preset'] ?? ''));

                return ['gridType' => $identifier, 'arLayoutPresetID' => $identifier];
        }
        $arguments = [
            'gridType' => 'FF',
            'isautomated' => empty($value['customWidths']) ? 1 : 0,
            'spacing' => (int) ($value['spacing'] ?? 0),
            'columns' => count($columns),
            'width' => [],
        ];
        foreach ($columns as $column) {
            $arguments['width'][] = (int) ($column['width'] ?? 0);
        }

        return $arguments;
    }

    /**
     * Get the layout of a block (NULL when it has none).
     */
    private function getLayout(Block $block): ?AreaLayout
    {
        $row = $this->getMainTableRow($block);
        $layout = empty($row['arLayoutID']) ? null : AreaLayout::getByID((int) $row['arLayoutID']);

        return $layout instanceof AreaLayout ? $layout : null;
    }

    /**
     * Get how the columns of a layout are sized.
     */
    private function getLayoutType(?AreaLayout $layout): string
    {
        if ($layout instanceof ThemeGridLayout) {
            return self::TYPE_THEME_GRID;
        }
        if ($layout instanceof PresetLayout) {
            return self::TYPE_PRESET;
        }

        return self::TYPE_CUSTOM;
    }

    /**
     * Get the identifier of a ready-made layout, checking that the site has one: it's the very
     * lookup that the controller does when it builds the layout.
     *
     * @throws \Concrete\Core\Error\UserMessageException
     */
    private function getPresetIdentifier(string $identifier): string
    {
        $preset = Preset::getByID($identifier);
        if ($preset === null) {
            throw new UserMessageException(t('No layout preset has the identifier %s.', $identifier));
        }

        return (string) $preset->getIdentifier();
    }
}
