<?php

declare(strict_types=1);

namespace Concrete\Block\ExpressEntryList;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The express_entry_list block type keeps a few of its settings as JSON documents, which the API
 * hands over as the lists they hold, and two more as serialized PHP objects, which the API shows
 * but doesn't let its clients write.
 */
class Api extends DefaultBlockApiHandler
{
    /**
     * The columns holding a JSON list of identifiers.
     *
     * @var string[]
     */
    private const LIST_COLUMNS = [
        'linkedProperties',
        'searchProperties',
        'searchAssociations',
    ];

    /**
     * The columns holding a serialized PHP object: a client sending one would have the site
     * rebuild whatever it describes.
     *
     * @var string[]
     */
    private const READONLY_COLUMNS = [
        'columns',
        'filterFields',
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
        foreach (self::READONLY_COLUMNS as $name) {
            $schema['properties'][$name]['readOnly'] = true;
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
        $current = $block === null ? [] : $this->getApiValue($block);
        $arguments = parent::getSaveArgumentsFromApiValue($value, $block);
        foreach (self::LIST_COLUMNS as $name) {
            $arguments[$name] = (array) ($value[$name] ?? $current[$name] ?? []);
        }
        foreach (self::READONLY_COLUMNS as $name) {
            $arguments[$name] = $current[$name] ?? '';
        }

        return $arguments;
    }
}
