<?php

declare(strict_types=1);

namespace Concrete\Block\EventList;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The event_list block type keeps the calendars whose events it lists in a single column, as a JSON
 * array: the API hands them over as the list they are. Its save() method is also told how the block
 * is set up, since it reads two fields that only its form sends.
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
        $schema['properties']['caID'] = [
            'type' => 'array',
            'description' => $schema['properties']['caID']['description'] ?? '',
            'items' => ['type' => 'integer'],
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
        $value['caID'] = $this->getCalendarIDs($value['caID'] ?? null);

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
        $arguments['caID'] = $this->getCalendarIDs($value['caID'] ?? $current['caID'] ?? null);
        // the save() method reads these two fields to tell what the block is bound to, as its form does
        $arguments['chooseCalendar'] = $arguments['caID'] === [] ? 'site' : 'specific';
        if (!empty($arguments['filterByTopicAttributeKeyID']) && !empty($arguments['filterByTopicID'])) {
            $arguments['filterByTopic'] = 'specific';
        } elseif ((string) ($arguments['filterByPageTopicAttributeKeyHandle'] ?? '') !== '') {
            $arguments['filterByTopic'] = 'page_attribute';
        } else {
            $arguments['filterByTopic'] = 'none';
        }

        return $arguments;
    }

    /**
     * Get the IDs of the calendars out of what the column holds, which is a JSON list or the ID of
     * a single calendar, or out of what a client sends.
     *
     * @return int[]
     */
    private function getCalendarIDs($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [$value];
        }
        $calendarIDs = [];
        foreach (is_array($value) ? $value : [] as $calendarID) {
            if (is_numeric($calendarID) && (int) $calendarID > 0) {
                $calendarIDs[] = (int) $calendarID;
            }
        }

        return $calendarIDs;
    }
}
