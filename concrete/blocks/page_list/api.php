<?php

declare(strict_types=1);

namespace Concrete\Block\PageList;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;
use Concrete\Core\Page\Feed;
use Concrete\Core\Tree\Node\Node;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The value of the page_list block type is the row of its table, plus the RSS feed it publishes:
 * the table only holds its ID, and the save() method of the block type gives up the feed when it
 * isn't told that the block has one.
 */
class Api extends DefaultBlockApiHandler
{
    /**
     * The fields of the feed, and what they are called in the schema.
     *
     * @var array<string,string>
     */
    private const FEED_FIELDS = [
        'rssHandle' => 'The handle of the RSS feed published by the block, which is the last part of its address.',
        'rssTitle' => 'The title of the RSS feed published by the block.',
        'rssDescription' => 'The description of the RSS feed published by the block.',
    ];

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $schema = parent::getApiValueSchema();
        foreach (self::FEED_FIELDS as $name => $description) {
            $schema['properties'][$name] = [
                'type' => 'string',
                'description' => $description . ' The block publishes a feed as soon as this field or pfID says so.',
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
        $feed = empty($value['pfID']) ? null : Feed::getByID((int) $value['pfID']);
        $value['rssHandle'] = $feed === null ? '' : (string) $feed->getHandle();
        $value['rssTitle'] = $feed === null ? '' : (string) $feed->getTitle();
        $value['rssDescription'] = $feed === null ? '' : (string) $feed->getDescription();

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
        foreach (array_keys(self::FEED_FIELDS) as $name) {
            $arguments[$name] = (string) ($value[$name] ?? $current[$name] ?? '');
        }
        $arguments['rss'] = (int) ($arguments['pfID'] ?? 0) === 0 && $arguments['rssHandle'] === '' ? 0 : 1;
        // the three settings of the custom topic only mean something together
        $topicAttributeKeyHandle = (string) ($arguments['customTopicAttributeKeyHandle'] ?? '');
        $topicTreeNodeID = (int) ($arguments['customTopicTreeNodeID'] ?? 0);
        if ($topicAttributeKeyHandle === '' || $topicTreeNodeID <= 0 || Node::getByID($topicTreeNodeID) === null) {
            $arguments['customTopicAttributeKeyHandle'] = '';
            $arguments['customTopicTreeNodeID'] = 0;
            $arguments['filterByCustomTopic'] = 0;
        }

        return $arguments;
    }
}
