<?php

declare(strict_types=1);

namespace Concrete\Attribute\Textarea;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\Model\AttributeKey as AttributeKeyModel;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Key\Settings\TextareaSettings;

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
        return 'AttributeKeyTextarea';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::describeApiKey()
     */
    public function describeApiKey(Key $key): AttributeKeyModel
    {
        $settings = $key->getAttributeKeySettings();
        $mode = $settings instanceof TextareaSettings ? (string) $settings->getMode() : '';
        $model = new AttributeKeyModel\Textarea();
        $this->fillApiKey($model, $key);
        // the controller shows as plain text the value of a key naming no mode of its own
        $model->mode = $mode === Controller::MODE_RICHTEXT ? Controller::MODE_RICHTEXT : Controller::MODE_TEXT;

        return $model;
    }
}
