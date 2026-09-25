<?php

declare(strict_types=1);

namespace Concrete\Block\DocumentLibrary;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The document_library block type keeps a few of its settings as JSON documents, which the API
 * hands over as the lists and the objects they hold; its save() method reads the file sets and the
 * folders under other names than the ones of the columns.
 */
class Api extends DefaultBlockApiHandler
{
    /**
     * The columns holding a JSON list, and the JSON type of the items.
     *
     * @var array<string,string>
     */
    private const LIST_COLUMNS = [
        'setIds' => 'integer',
        'expandableProperties' => 'string',
        'searchProperties' => 'string',
    ];

    /**
     * The columns holding a JSON object whose values are strings.
     *
     * @var string[]
     */
    private const MAP_COLUMNS = [
        'viewProperties',
    ];

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $schema = parent::getApiValueSchema();
        foreach (self::LIST_COLUMNS as $name => $itemType) {
            $schema['properties'][$name] = [
                'type' => 'array',
                'description' => $schema['properties'][$name]['description'] ?? '',
                'items' => ['type' => $itemType],
            ];
        }
        foreach (self::MAP_COLUMNS as $name) {
            $schema['properties'][$name] = [
                'type' => 'object',
                'description' => $schema['properties'][$name]['description'] ?? '',
                'additionalProperties' => ['type' => 'string'],
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
        foreach (self::LIST_COLUMNS as $name => $itemType) {
            $value[$name] = array_values(array_map($itemType === 'integer' ? 'intval' : 'strval', $this->decodeJson($value[$name] ?? null)));
        }
        foreach (self::MAP_COLUMNS as $name) {
            $value[$name] = array_map('strval', $this->decodeJson($value[$name] ?? null));
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
        foreach (array_merge(array_keys(self::LIST_COLUMNS), self::MAP_COLUMNS) as $name) {
            $arguments[$name] = (array) ($value[$name] ?? $current[$name] ?? []);
        }
        // the save() method reads the file sets and the folders the way the form of the block type sends them
        $arguments['fsID'] = $arguments['setIds'];
        $arguments['showFolders'] = empty($arguments['hideFolders']) ? 1 : 0;

        return $arguments;
    }

    /**
     * Get what a column holding a JSON document says.
     *
     * @return array<int|string,mixed>
     */
    private function decodeJson($value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return is_array($value) ? $value : [];
    }
}
