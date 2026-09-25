<?php

declare(strict_types=1);

namespace Concrete\Block\Survey;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;
use Concrete\Core\Database\Connection\Connection;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The survey block type keeps the answers the visitors can pick in a table of their own, and their
 * votes in another one: the value carries the answers, and says nothing about the votes, which are
 * cast by the visitors and belong to them.
 */
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
        $schema['properties']['answers'] = [
            'type' => 'array',
            'description' => 'The answers the visitors can pick, in the order they are displayed. An answer that is already there keeps its votes and its place, an answer that is not comes after the others, and an answer that is left out is deleted along with its votes.',
            'items' => ['type' => 'string'],
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
        $answers = app(Connection::class)->fetchFirstColumn(
            'SELECT optionName FROM btSurveyOptions WHERE bID = ? ORDER BY displayOrder ASC',
            [$block->getBlockID()]
        );
        $value['answers'] = array_map('strval', $answers);

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
        $answers = [];
        foreach ((array) ($value['answers'] ?? $current['answers'] ?? []) as $answer) {
            $answer = (string) $answer;
            if ($answer !== '') {
                $answers[] = $answer;
            }
        }
        // the save() method keeps the answers it's told to keep, and adds the ones it's given
        $arguments['survivingOptionNames'] = $answers;
        $arguments['pollOption'] = array_values(array_diff($answers, $current['answers'] ?? []));

        return $arguments;
    }
}
