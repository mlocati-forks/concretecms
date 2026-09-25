<?php

declare(strict_types=1);

namespace Concrete\Block\Calendar;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The calendar block type keeps the views it offers, their order and what the lightbox of an event
 * shows as JSON documents: the API hands them over as the lists they are.
 */
class Api extends DefaultBlockApiHandler
{
    /**
     * The columns holding a JSON list of strings.
     *
     * @var string[]
     */
    private const LIST_COLUMNS = [
        'viewTypes',
        'viewTypesOrder',
        'lightboxProperties',
    ];

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $schema = parent::getApiValueSchema();
        foreach (self::LIST_COLUMNS as $name) {
            $schema['properties'][$name] = [
                'type' => 'array',
                'description' => $schema['properties'][$name]['description'] ?? '',
                'items' => ['type' => 'string'],
            ];
        }

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
        foreach (self::LIST_COLUMNS as $name) {
            $decoded = is_string($value[$name] ?? null) ? json_decode($value[$name], true) : null;
            $value[$name] = is_array($decoded) ? array_values(array_map('strval', $decoded)) : [];
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
        $arguments = parent::getSaveArgumentsFromApiValue($value, $block);
        $current = $block === null ? [] : $this->getApiValue($block);
        foreach (self::LIST_COLUMNS as $name) {
            $list = $value[$name] ?? $current[$name] ?? [];
            // the save() method writes these columns as they are when the data doesn't come from its form
            $arguments[$name] = json_encode(array_values(is_array($list) ? $list : []));
        }

        return $arguments;
    }
}
