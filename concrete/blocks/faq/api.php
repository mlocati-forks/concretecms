<?php

declare(strict_types=1);

namespace Concrete\Block\Faq;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;
use Concrete\Core\Editor\LinkAbstractor;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The faq block type keeps its questions in a table of their own: the value carries them along with
 * the row of the main table, and its save() method reads them as the parallel lists that its form
 * sends, one for every field.
 */
class Api extends DefaultBlockApiHandler
{
    /**
     * The table holding the questions.
     *
     * @var string
     */
    private const ENTRIES_TABLE = 'btFaqEntries';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $schema = parent::getApiValueSchema();
        $schema['properties']['questions'] = $this->describeTableRows(self::ENTRIES_TABLE, 'The questions of the block.');

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
        $value['questions'] = $this->getTableRows($block, self::ENTRIES_TABLE, 'sortOrder');

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
        $arguments += ['title' => [], 'linkTitle' => [], 'description' => [], 'sortOrder' => []];
        $sortOrder = 0;
        foreach ((array) ($value['questions'] ?? $current['questions'] ?? []) as $question) {
            if (!is_array($question)) {
                continue;
            }
            $arguments['title'][] = (string) ($question['title'] ?? '');
            $arguments['linkTitle'][] = (string) ($question['linkTitle'] ?? '');
            // the save() method abstracts the rich text the way the editor of the form sends it
            $arguments['description'][] = LinkAbstractor::translateFromEditMode((string) ($question['description'] ?? ''));
            // the questions are numbered after their place in the list, which is the order they come in
            $arguments['sortOrder'][] = $sortOrder++;
        }

        return $arguments;
    }
}
