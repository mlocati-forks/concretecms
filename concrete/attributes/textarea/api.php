<?php

declare(strict_types=1);

namespace Concrete\Attribute\Textarea;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\FileIdentifier;
use Concrete\Core\Api\Model\AttributeKey as AttributeKeyModel;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Key\Settings\TextareaSettings;
use Concrete\Core\Reference\ReferenceKind;

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

    /**
     * {@inheritdoc}
     *
     * The HTML of a rich text value names the files it refers to the way the one of a content block
     * does, so that what a client reads of the one can be written into the other.
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiValue()
     */
    public function getApiValue()
    {
        $value = parent::getApiValue();
        $key = $this->controller->getAttributeKey();
        if (!is_string($value) || $value === '' || !$this->isRichText($key instanceof Key ? $key : null)) {
            return $value;
        }

        return app(FileIdentifier::class)->contentForApi($value);
    }

    /**
     * {@inheritdoc}
     *
     * Only the HTML of a rich text key names something of the site: a plain text one holds words.
     *
     * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::getApiValueReference()
     */
    public function getApiValueReference(Key $key): string
    {
        return $this->isRichText($key) ? ReferenceKind::CONTENT : '';
    }

    /**
     * Whether the key holds HTML instead of the plain text that the site shows as it is.
     */
    private function isRichText(?Key $key): bool
    {
        $settings = $key === null ? null : $key->getAttributeKeySettings();

        return $settings instanceof TextareaSettings && (string) $settings->getMode() === Controller::MODE_RICHTEXT;
    }
}
