<?php

declare(strict_types=1);

namespace Concrete\Attribute\Topics;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\Model\AttributeKey as AttributeKeyModel;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Key\Settings\TopicsSettings;

defined('C5_EXECUTE') or die('Access Denied.');

class Api extends AttributeApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiKeySchema()
     */
    public function getApiKeySchema(): string
    {
        return 'AttributeKeyTopics';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): string
    {
        return 'AttributeValueTopics';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::describeApiKey()
     */
    public function describeApiKey(Key $key): AttributeKeyModel
    {
        $settings = $key->getAttributeKeySettings();
        $model = new AttributeKeyModel\Topics();
        $this->fillApiKey($model, $key);
        // a value names the nodes of one tree, which the topic trees endpoint hands over
        $model->topic_tree_id = $settings instanceof TopicsSettings ? (int) $settings->getTopicTreeID() : 0;
        $model->parent_node_id = $settings instanceof TopicsSettings ? (int) $settings->getParentNodeID() : 0;
        $model->allow_multiple_values = $settings instanceof TopicsSettings ? (bool) $settings->allowMultipleValues() : false;

        return $model;
    }

    /**
     * {@inheritdoc}
     *
     * A value names the topics by the ID of their node, which is what a read of them hands over.
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::createApiValue()
     */
    public function createApiValue($value)
    {
        $ids = [];
        foreach ((array) self::unwrapApiData($value) as $item) {
            $id = self::extractApiIdentifier($item, 'id');
            if ($id !== false) {
                // the controller keeps the nodes that are there and leaves out what names none
                $ids[] = $id;
            }
        }

        return parent::createApiValue($ids);
    }
}
