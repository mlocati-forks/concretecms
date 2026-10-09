<?php

declare(strict_types=1);

namespace Concrete\Attribute\DateTime;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\Model\AttributeKey as AttributeKeyModel;
use Concrete\Core\Api\OpenApi\SpecProperty;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Key\Settings\DateTimeSettings;

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
        return 'AttributeKeyDateTime';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::describeApiKey()
     */
    public function describeApiKey(Key $key): AttributeKeyModel
    {
        $settings = $key->getAttributeKeySettings();
        $mode = $settings instanceof DateTimeSettings ? (string) $settings->getMode() : '';
        $model = new AttributeKeyModel\DateTime();
        $this->fillApiKey($model, $key);
        // a key naming no mode keeps a date and a time, the way its controller shows it
        $model->mode = $mode === '' ? 'date_time' : $mode;

        return $model;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiSpecProperty()
     */
    public function getApiSpecProperty(Key $key): SpecProperty
    {
        // no format: a value is whatever strtotime() reads, and it comes back as Y-m-d H:i:s
        return new SpecProperty(
            (string) $key->getAttributeKeyHandle(),
            (string) $key->getAttributeKeyDisplayName(),
            'string'
        );
    }
}
