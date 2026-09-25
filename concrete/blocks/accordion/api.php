<?php

declare(strict_types=1);

namespace Concrete\Block\Accordion;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;
use Concrete\Core\Editor\LinkAbstractor;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The accordion block type keeps its items in a table of their own: the value carries them along
 * with the row of the main table, and its save() method reads them as the JSON document that its
 * form builds.
 */
class Api extends DefaultBlockApiHandler
{
    /**
     * The table holding the items.
     *
     * @var string
     */
    private const ENTRIES_TABLE = 'btAccordionEntries';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $schema = parent::getApiValueSchema();
        $schema['properties']['items'] = $this->describeTableRows(self::ENTRIES_TABLE, 'The items of the accordion.');

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
        $value['items'] = $this->getTableRows($block, self::ENTRIES_TABLE, 'sortOrder');

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
        $items = [];
        foreach ((array) ($value['items'] ?? $current['items'] ?? []) as $item) {
            if (is_array($item)) {
                $items[] = [
                    'title' => (string) ($item['title'] ?? ''),
                    // the save() method abstracts the rich text the way the editor of the form sends it
                    'description' => LinkAbstractor::translateFromEditMode((string) ($item['description'] ?? '')),
                ];
            }
        }
        $arguments['accordionBlockData'] = json_encode($items);

        return $arguments;
    }
}
