<?php

declare(strict_types=1);

namespace Concrete\Block\Image;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Reference\ReferenceKind;

defined('C5_EXECUTE') or die('Access Denied.');

class Api extends DefaultBlockApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $schema = parent::getApiValueSchema();
        $schema['properties']['breakpoints'] = [
            'type' => 'object',
            'description' => implode("\n", [
                'The thumbnail type displayed at every breakpoint of the theme: the key is the handle of the breakpoint, as the image_breakpoints of GET /page_themes name them (used if sizingOption is thumbnails_configurable).',
                $this->describeReference(ReferenceKind::THUMBNAIL_TYPE),
            ]),
            'additionalProperties' => [
                'type' => 'integer',
                'x-concrete-reference' => ReferenceKind::THUMBNAIL_TYPE,
            ],
        ];

        return $schema;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValue()
     */
    public function getApiValue(Block $block): array
    {
        $value = parent::getApiValue($block);
        $breakpoints = app(Connection::class)->fetchAllKeyValue(
            'SELECT breakpointHandle, ftTypeID FROM btContentImageBreakpoints WHERE bID = ? ORDER BY id ASC',
            [$block->getBlockID()]
        );
        $value['breakpoints'] = array_map('intval', $breakpoints);

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getSaveArgumentsFromApiValue()
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        $arguments = parent::getSaveArgumentsFromApiValue($value, $block);
        $current = $block === null ? [] : $this->getApiValue($block);
        $breakpoints = [];
        foreach ((array) ($value['breakpoints'] ?? $current['breakpoints'] ?? []) as $handle => $thumbnailTypeID) {
            $breakpoints[(string) $handle] = (int) $thumbnailTypeID;
        }
        $arguments['selectedThumbnailTypes'] = $breakpoints;

        return $arguments;
    }
}
